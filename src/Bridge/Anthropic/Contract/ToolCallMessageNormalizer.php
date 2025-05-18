<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ToolCallMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ToolCallMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ToolCallMessage::class => true,
        ];
    }

    /**
     * @param ToolCallMessage $data
     *
     * @return array{
     *     role: 'user',
     *     content: list<array{
     *         type: 'tool_result',
     *         tool_use_id: string,
     *         content: string,
     *     }>
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'tool_result',
                    'tool_use_id' => $data->toolCall->id,
                    'content' => $data->content,
                ],
            ],
        ];
    }
}
