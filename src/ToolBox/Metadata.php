<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

/**
 * @phpstan-import-type ParameterDefinition from ParameterAnalyzer
 */
final readonly class Metadata implements \JsonSerializable
{
    /**
     * @param ParameterDefinition|null $parameters
     * @param array<mixed>|null        $responseFormat
     */
    public function __construct(
        public string $className,
        public string $name,
        public string $description,
        public string $method,
        public ?array $parameters,
        public ?array $responseFormat = null,
    ) {
    }

    /**
     * @return array{
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         description: string,
     *         parameters?: ParameterDefinition,
     *         responseFormat?: array<mixed>|null
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

        if (null !== $this->responseFormat) {
            $function['responseFormat'] = $this->responseFormat;
        }

        return [
            'type' => 'function',
            'function' => $function,
        ];
    }
}
