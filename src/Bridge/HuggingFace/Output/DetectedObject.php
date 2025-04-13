<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\HuggingFace\Output;

final readonly class DetectedObject
{
    public function __construct(
        public string $label,
        public float $score,
        public float $xmin,
        public float $ymin,
        public float $xmax,
        public float $ymax,
    ) {
    }
}
