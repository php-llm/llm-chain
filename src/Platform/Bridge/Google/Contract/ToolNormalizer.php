<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @author Valtteri R <valtzu@gmail.com>
 *
 * @phpstan-import-type JsonSchema from Factory
 */
final class ToolNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return Tool::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Gemini;
    }

    /**
     * @param Tool $data
     *
     * @return array{
     *     functionDeclarations: array{
     *         name: string,
     *         description: string,
     *         parameters: JsonSchema|array{type: 'object'}
     *     }[]
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $parameters = $data->parameters;
        unset($parameters['additionalProperties']);

        return [
            'functionDeclarations' => [
                [
                    'description' => $data->description,
                    'name' => $data->name,
                    'parameters' => $parameters,
                ],
            ],
        ];
    }
}
