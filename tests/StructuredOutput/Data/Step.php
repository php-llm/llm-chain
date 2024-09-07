<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\StructuredOutput\Data;

final class Step
{
    public function __construct(
        public string $explanation,
        public string $output,
    ) {
    }
}
