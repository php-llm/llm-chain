<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Tool;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ExecutionReference
{
    public function __construct(
        public string $class,
        public string $method = '__invoke',
    ) {
    }
}
