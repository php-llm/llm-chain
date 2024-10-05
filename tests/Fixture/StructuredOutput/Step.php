<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\StructuredOutput;

final class Step
{
    public function __construct(
        public string $explanation,
        public string $output,
    ) {
    }
}
