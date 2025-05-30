<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Model;

use function Symfony\Component\String\u;

final class ImageNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return Image::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Claude;
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
