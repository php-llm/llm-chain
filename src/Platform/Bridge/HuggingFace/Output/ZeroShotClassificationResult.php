<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final class ZeroShotClassificationResult
{
    /**
     * @param array<string> $labels
     * @param array<float>  $scores
     */
    public function __construct(
        public array $labels,
        public array $scores,
        public ?string $sequence = null,
    ) {
    }

    /**
     * @param array{labels: array<string>, scores: array<float>, sequence?: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['labels'],
            $data['scores'],
            $data['sequence'] ?? null,
        );
    }
}
