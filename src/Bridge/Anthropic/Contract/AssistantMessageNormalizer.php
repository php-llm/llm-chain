<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AssistantMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AssistantMessage && $data->hasToolCalls();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AssistantMessage::class => true,
        ];
    }

    /**
     * @param AssistantMessage $data
     *
     * @return array{
     *     role: 'assistant',
     *     content: list<array{
     *         type: 'tool_use',
     *         id: string,
     *         name: string,
     *         input: array<string, mixed>
     *     }>
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'role' => 'assistant',
            'content' => array_map(static function (ToolCall $toolCall) {
                return [
                    'type' => 'tool_use',
                    'id' => $toolCall->id,
                    'name' => $toolCall->name,
                    'input' => empty($toolCall->arguments) ? new \stdClass() : $toolCall->arguments,
                ];
            }, $data->toolCalls),
        ];
    }
}
