<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\GPT;

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
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
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof GPT;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return $this->convertStream($response);
        }

        $data = json_decode($response->getContent(false), true);

        if (!isset($data['choices'])) {
            throw new RuntimeException('Response does not contain choices');
        }

        /** @var Choice[] $choices */
        $choices = array_map([$this, 'convertChoice'], $data['choices']);

        if (1 !== count($choices)) {
            return new ChoiceResponse(...$choices);
        }

        if ($choices[0]->hasToolCall()) {
            return new ToolCallResponse(...$choices[0]->getToolCalls());
        }

        return new TextResponse($choices[0]->getContent());
    }

    private function convertStream(HttpResponse $response): ToolCallResponse|StreamResponse
    {
        $stream = $this->streamResponse($response);

        if ($this->streamIsToolCall($stream)) {
            return new ToolCallResponse(...$this->convertStreamToToolCalls($stream));
        } else {
            return new StreamResponse($this->convertStreamContent($stream));
        }
    }

    private function streamResponse(HttpResponse $response): \Generator
    {
        foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
            if (!$chunk instanceof ServerSentEvent || '[DONE]' === $chunk->getData()) {
                continue;
            }

            try {
                yield $chunk->getArrayData();
            } catch (JsonException) {
                // try catch only needed for Symfony 6.4
                continue;
            }
        }
    }

    private function streamIsToolCall(\Generator $response): bool
    {
        $data = $response->current();

        return isset($data['choices'][0]['delta']['tool_calls']);
    }

    /**
     * @return ToolCall[]
     */
    private function convertStreamToToolCalls(\Generator $response): array
    {
        $toolCalls = [];
        foreach ($response as $data) {
            if (!isset($data['choices'][0]['delta']['tool_calls'])) {
                continue;
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
        }

        return array_map([$this, 'convertToolCall'], $toolCalls);
    }

    private function convertStreamContent(\Generator $generator): \Generator
    {
        foreach ($generator as $data) {
            if (!isset($data['choices'][0]['delta']['content'])) {
                continue;
            }

            yield $data['choices'][0]['delta']['content'];
        }
    }

    /**
     * @param array{
     *     index: integer,
     *     message: array{
     *         role: 'assistant',
     *         content: ?string,
     *         tool_calls: array{
     *             id: string,
     *             type: 'function',
     *             function: array{
     *                 name: string,
     *                 arguments: string
     *             },
     *         },
     *         refusal: ?mixed
     *     },
     *     logprobs: string,
     *     finish_reason: 'stop'|'length'|'tool_calls'|'content_filter',
     * } $choice
     */
    private function convertChoice(array $choice): Choice
    {
        if ('tool_calls' === $choice['finish_reason']) {
            return new Choice(toolCalls: array_map([$this, 'convertToolCall'], $choice['message']['tool_calls']));
        }

        if ('stop' === $choice['finish_reason']) {
            return new Choice($choice['message']['content']);
        }

        throw new RuntimeException(sprintf('Unsupported finish reason "%s".', $choice['finish_reason']));
    }

    /**
     * @param array{
     *     id: string,
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         arguments: string
     *     }
     * } $toolCall
     */
    private function convertToolCall(array $toolCall): ToolCall
    {
        $arguments = json_decode($toolCall['function']['arguments'], true, JSON_THROW_ON_ERROR);

        return new ToolCall($toolCall['id'], $toolCall['function']['name'], $arguments);
    }
}
