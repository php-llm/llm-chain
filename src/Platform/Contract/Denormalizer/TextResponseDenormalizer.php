<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Denormalizer;

use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TextResponseDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): LlmResponse
    {
        // Delegate to the platform-specific parser, which now returns the final Response object directly
        return $this->denormalizer->denormalize($data, LlmResponse::class, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return LlmResponse::class === $type && !($context[ResponseContract::CONTEXT_OPTIONS]['stream'] ?? false);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [LlmResponse::class => false];
    }
}
