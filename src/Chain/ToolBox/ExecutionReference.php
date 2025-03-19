<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

final class ExecutionReference
{
    public function __construct(
        public string $class,
        public string $method = '__invoke',
    ) {
    }
}
