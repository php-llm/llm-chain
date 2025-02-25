<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\JsonSchema\Factory;

/**
 * @phpstan-import-type JsonSchema from Factory
 */
final readonly class Metadata implements \JsonSerializable
{
    /**
     * @param JsonSchema|null $parameters
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
     *         parameters?: JsonSchema
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
