<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Model\Message\Content\Image;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function Symfony\Component\String\u;

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
     * @return array{
     *     type: 'image',
     *     source: array{
     *         type: 'base64',
     *         media_type: string,
     *         data: string
     *     }
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => u($data->getFormat())->replace('jpg', 'jpeg')->toString(),
                'data' => $data->asBase64(),
            ],
        ];
    }
}
