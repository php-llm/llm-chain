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
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
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
