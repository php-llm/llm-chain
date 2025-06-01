<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Nova;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @phpstan-import-type JsonSchema from Factory
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
class ToolNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return Tool::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Nova;
    }

    /**
     * @param Tool $data
     *
     * @return array{
     *     toolSpec: array{
     *         name: string,
     *         description: string,
     *         inputSchema: array{
     *             json: JsonSchema|array{type: 'object'}
     *         }
     *     }
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'toolSpec' => [
                'name' => $data->name,
                'description' => $data->description,
                'inputSchema' => [
                    'json' => $data->parameters ?? new \stdClass(),
                ],
            ],
        ];
    }
}
