<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
     * @return array{
     *     role: 'tool',
     *     content: string,
     *     tool_call_id: string,
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'role' => $data->getRole()->value,
            'content' => $this->normalizer->normalize($data->content, $format, $context),
            'tool_call_id' => $data->toolCall->id,
        ];
    }
}
