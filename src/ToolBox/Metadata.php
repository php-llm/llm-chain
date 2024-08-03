<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

final class Metadata
{
    public function __construct(
        public readonly string $className,
        public readonly string $name,
        public readonly string $description,
        public readonly string $method,
        public readonly ?array $parameters,
    ) {
    }
}
