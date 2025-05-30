<?php

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer;

use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type JsonSchema from Factory
 */
class ToolNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Tool;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Tool::class => true,
        ];
    }

    /**
     * @param Tool $data
     *
     * @return array{
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         description: string,
     *         parameters?: JsonSchema
     *     }
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $function = [
            'name' => $data->name,
            'description' => $data->description,
        ];

        if (isset($data->parameters)) {
            $function['parameters'] = $data->parameters;
        }

        return [
            'type' => 'function',
            'function' => $function,
        ];
    }
}
