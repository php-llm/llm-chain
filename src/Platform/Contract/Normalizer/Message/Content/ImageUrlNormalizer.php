<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\ImageUrl;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageUrlNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ImageUrl;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ImageUrl::class => true,
        ];
    }

    /**
     * @param ImageUrl $data
     *
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'type' => 'image_url',
            'image_url' => ['url' => $data->url],
        ];
    }
}
