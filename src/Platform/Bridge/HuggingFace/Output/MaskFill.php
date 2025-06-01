<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
