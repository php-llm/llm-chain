<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

/**
 * @phpstan-import-type ParameterDefinition from ParameterAnalyzer
 */
final readonly class Metadata implements \JsonSerializable
{
    /**
     * @param ParameterDefinition|null $parameters
     */
    public function __construct(
        public string $className,
        public string $name,
        public string $description,
        public string $method,
        public ?array $parameters,
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
