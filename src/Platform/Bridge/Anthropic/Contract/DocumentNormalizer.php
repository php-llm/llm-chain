<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\Document;
use PhpLlm\LlmChain\Platform\Model;

class DocumentNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return Document::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Claude;
    }

    /**
     * @param Document $data
     *
     * @return array{type: 'document', source: array{type: 'base64', media_type: string, data: string}}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'type' => 'document',
            'source' => [
                'type' => 'base64',
                'media_type' => $data->getFormat(),
                'data' => $data->asBase64(),
            ],
        ];
    }
}
