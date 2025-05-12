<?php

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Contract;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\File;
use PhpLlm\LlmChain\Platform\Model;

class FileNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return File::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return true;
    }

    /**
     * @param File $data
     *
     * @return array{
     *     headers: array<'Content-Type', string>,
     *     body: string
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'headers' => ['Content-Type' => $data->getFormat()],
            'body' => $data->asBinary(),
        ];
    }
}
