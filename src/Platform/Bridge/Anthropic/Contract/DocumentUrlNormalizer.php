<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\DocumentUrl;
use PhpLlm\LlmChain\Platform\Model;

final class DocumentUrlNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return DocumentUrl::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Claude;
    }

    /**
     * @param DocumentUrl $data
     *
     * @return array{type: 'document', source: array{type: 'url', url: string}}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'type' => 'document',
            'source' => [
                'type' => 'url',
                'url' => $data->url,
            ],
        ];
    }
}
