<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final readonly class MaskFill
{
    public function __construct(
        public int $token,
        public string $tokenStr,
        public string $sequence,
        public float $score,
    ) {
    }
}
