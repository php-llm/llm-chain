<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @phpstan-import-type JsonSchema from Factory
 */
class ToolNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return Tool::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Claude;
    }

    /**
     * @param Tool $data
     *
     * @return array{
     *     name: string,
     *     description: string,
     *     input_schema: JsonSchema|array{type: 'object'}
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'name' => $data->name,
            'description' => $data->description,
            'input_schema' => $data->parameters ?? ['type' => 'object'],
        ];
    }
}
