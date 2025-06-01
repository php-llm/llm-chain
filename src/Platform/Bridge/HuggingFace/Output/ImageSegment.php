<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ImageSegment
{
    public function __construct(
        public string $label,
        public ?float $score,
        public string $mask,
    ) {
    }
}
