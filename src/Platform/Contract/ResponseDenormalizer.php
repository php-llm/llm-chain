<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract;

use PhpLlm\LlmChain\Platform\Contract\Denormalizer\StreamResponseDenormalizer;
use PhpLlm\LlmChain\Platform\Contract\Denormalizer\TextResponseDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class ResponseDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private readonly Serializer $serializer;

    public function __construct(DenormalizerInterface ...$denormalizers)
    {
        $this->serializer = new Serializer([
            ...$denormalizers,
            // Base denormalizers
            new TextResponseDenormalizer(),
            new StreamResponseDenormalizer(),
        ]);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $this->serializer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->serializer->supportsDenormalization($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->serializer->getSupportedTypes($format);
    }
}
