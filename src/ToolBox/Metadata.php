<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

/**
 * @phpstan-import-type ParameterDefinition from ParameterAnalyzer
 */
final class Metadata
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
}
