<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final readonly class ImageSegment
{
    public function __construct(
        public string $label,
        public ?float $score,
        public string $mask,
    ) {
    }
}
