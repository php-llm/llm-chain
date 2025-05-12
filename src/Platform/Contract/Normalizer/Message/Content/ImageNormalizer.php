<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Platform\Message\Content\Image;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Image;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Image::class => true,
        ];
    }

    /**
     * @param Image $data
     *
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'type' => 'image_url',
            'image_url' => ['url' => $data->asDataUrl()],
        ];
    }
}
