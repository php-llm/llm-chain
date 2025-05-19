<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\AwsBedrock\Language;

use PhpLlm\LlmChain\Bridge\AwsBedrock\BedrockLanguageModel;
use PhpLlm\LlmChain\Exception\ContentFilterException;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\Choice;
use PhpLlm\LlmChain\Model\Response\ChoiceResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\StreamResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof BedrockLanguageModel;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return new StreamResponse($this->convertStream($response));
        }

        try {
            $data = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            if (400 === $response->getStatusCode()) {
                throw new ContentFilterException(message: 'Validation error', previous: $e);
            }

            throw $e;
        }

        if (!isset($data['output']['message']['content'])) {
            throw new RuntimeException('Response does not contain choices');
        }

        $stopReason = $data['stopReason'];

        /** @var Choice[] $choices */
        $choices = array_values(
            array_filter(
                array_map(
                    fn ($content) => $this->convertChoice(
                        $content, $stopReason
                    ),
                    $data['output']['message']['content']
                ),
                function (Choice $choiceEntry) use (&$stopReason) {
                    if ('tool_use' === $stopReason) {
                        return $choiceEntry->hasToolCall();
                    }

                    return true;
                }
            )
        );

        if (1 !== count($choices)) {
            return new ChoiceResponse(...$choices);
        }

        if ($choices[0]->hasToolCall()) {
            return new ToolCallResponse(...$choices[0]->getToolCalls());
        }

        return new TextResponse($choices[0]->getContent());
    }

    private function convertStream(HttpResponse $response): \Generator
    {
        $toolCalls = [];
        foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
            if (!$chunk instanceof ServerSentEvent || '[DONE]' === $chunk->getData()) {
                continue;
            }

            try {
                $data = $chunk->getArrayData();
            } catch (JsonException) {
                // try catch only needed for Symfony 6.4
                continue;
            }

            if ($this->streamIsToolCall($data)) {
                $toolCalls = $this->convertStreamToToolCalls($toolCalls, $data);
            }

            if ([] !== $toolCalls && $this->isToolCallsStreamFinished($data)) {
                yield new ToolCallResponse(...\array_map($this->convertToolCall(...), $toolCalls));
            }

            if (!isset($data['choices'][0]['delta']['content'])) {
                continue;
            }

            yield $data['choices'][0]['delta']['content'];
        }
    }

    /**
     * @param array<string, mixed> $toolCalls
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function convertStreamToToolCalls(array $toolCalls, array $data): array
    {
        if (!isset($data['choices'][0]['delta']['tool_calls'])) {
            return $toolCalls;
        }

        foreach ($data['choices'][0]['delta']['tool_calls'] as $i => $toolCall) {
            if (isset($toolCall['id'])) {
                // initialize tool call
                $toolCalls[$i] = [
                    'id' => $toolCall['id'],
                    'function' => $toolCall['function'],
                ];
                continue;
            }

            // add arguments delta to tool call
            $toolCalls[$i]['function']['arguments'] .= $toolCall['function']['arguments'];
        }

        return $toolCalls;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function streamIsToolCall(array $data): bool
    {
        return isset($data['choices'][0]['delta']['tool_calls']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function isToolCallsStreamFinished(array $data): bool
    {
        return isset($data['choices'][0]['finish_reason']) && 'tool_calls' === $data['choices'][0]['finish_reason'];
    }

    private function convertChoice(array $choice, string $stopReason): Choice
    {
        if (isset($choice['toolUse'])) {
            return new Choice(
                toolCalls: [
                    $this->convertToolCall($choice['toolUse']),
                ]
            );
        }

        if (isset($choice['text'])) {
            return new Choice(
                $choice['text']
            );
        }

        throw new RuntimeException(sprintf('Unsupported finish reason "%s".', $stopReason));
    }

    private function convertToolCall(array $toolCall): ToolCall
    {
        return new ToolCall($toolCall['toolUseId'], $toolCall['name'], $toolCall['input']);
    }
}
