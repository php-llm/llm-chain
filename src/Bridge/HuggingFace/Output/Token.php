<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\HuggingFace\Output;

final readonly class Token
{
    public function __construct(
        public string $entityGroup,
        public float $score,
        public string $word,
        public int $start,
        public int $end,
    ) {
    }
}
