<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Mistral\ResponseContract;

use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Contract\Denormalizer\ModelContractDenormalizer;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\Choice;
use PhpLlm\LlmChain\Platform\Response\ChoiceResponse;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;

final class MistralResponseParser extends ModelContractDenormalizer
{
    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Mistral;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): LlmResponse
    {
        if (!isset($data['choices'])) {
            throw new \RuntimeException('Invalid Mistral response structure: missing choices');
        }

        $choices = array_map([$this, 'parseChoice'], $data['choices']);

        // For single choice responses, determine response type based on content
        if (1 === \count($choices)) {
            $choice = $choices[0];

            if ($choice->hasToolCall()) {
                return new ToolCallResponse(...$choice->getToolCalls());
            }

            return new TextResponse($choice->getContent() ?? '');
        }

        // Multiple choices always return ChoiceResponse
        return new ChoiceResponse(...$choices);
    }

    /**
     * @param array<string, mixed> $choice
     */
    private function parseChoice(array $choice): Choice
    {
        $message = $choice['message'] ?? [];

        return new Choice(
            content: $message['content'] ?? null,
            toolCalls: array_map([$this, 'parseToolCall'], $message['tool_calls'] ?? []),
        );
    }

    /**
     * @param array<string, mixed> $toolCall
     */
    private function parseToolCall(array $toolCall): ToolCall
    {
        return new ToolCall(
            id: $toolCall['id'],
            name: $toolCall['function']['name'],
            arguments: json_decode($toolCall['function']['arguments'], true, flags: \JSON_THROW_ON_ERROR),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return LlmResponse::class === $type && parent::supportsDenormalization($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [LlmResponse::class => false];
    }
}
