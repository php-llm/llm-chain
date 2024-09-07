<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\StructuredOutput\Data;

final class MathReasoning
{
    /**
     * @param Step[] $steps
     */
    public function __construct(
        public array $steps,
        public string $finalAnswer,
    ) {
    }
}
