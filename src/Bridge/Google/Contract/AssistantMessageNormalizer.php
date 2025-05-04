<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google\Contract;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AssistantMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AssistantMessage;
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
     * @return array{array{text: string}}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            ['text' => $data->content],
        ];
    }
}
