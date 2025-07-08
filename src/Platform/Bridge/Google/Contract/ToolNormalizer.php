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
     *     name: string,
     *     description: string,
     *     parameters: JsonSchema|array{type: 'object'}
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'description' => $data->description,
            'name' => $data->name,
            'parameters' => $data->parameters ? $this->removeAdditionalProperties($data->parameters) : null,
        ];
    }

    /**
     * @template T of array
     *
     * @phpstan-param T $data
     *
     * @phpstan-return T
     */
    private function removeAdditionalProperties(array $data): array
    {
        unset($data['additionalProperties']); // not supported by Gemini

        foreach ($data as &$value) {
            if (\is_array($value)) {
                $value = $this->removeAdditionalProperties($value);
            }
        }

        return $data;
    }
}
