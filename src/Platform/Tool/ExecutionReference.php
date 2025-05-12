<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Tool;

final class ExecutionReference
{
    public function __construct(
        public string $class,
        public string $method = '__invoke',
    ) {
    }
}
