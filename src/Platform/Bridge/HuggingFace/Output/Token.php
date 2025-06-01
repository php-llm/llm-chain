<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
