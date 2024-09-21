<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

/**
 * @phpstan-import-type ParameterDefinition from ParameterAnalyzer
 */
final class Metadata implements \JsonSerializable
{
    /**
     * @param ParameterDefinition|null $parameters
     */
    public function __construct(
        public readonly string $className,
        public readonly string $name,
        public readonly string $description,
        public readonly string $method,
        public readonly ?array $parameters,
    ) {
    }

    /**
     * @return array{
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         description: string,
     *         parameters?: ParameterDefinition
     *     }
     * }
     */
    public function jsonSerialize(): array
    {
        $function = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if (isset($this->parameters)) {
            $function['parameters'] = $this->parameters;
        }

        return [
            'type' => 'function',
            'function' => $function,
        ];
    }
}
