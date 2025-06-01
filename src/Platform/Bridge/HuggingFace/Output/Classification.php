<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Classification
{
    public function __construct(
        public string $label,
        public float $score,
    ) {
    }
}
