<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final class ImageSegmentationResult
{
    /**
     * @param ImageSegment[] $segments
     */
    public function __construct(
        public array $segments,
    ) {
    }

    /**
     * @param array<array{label: string, score: float, mask: string}> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            array_map(fn (array $item) => new ImageSegment($item['label'], $item['score'], $item['mask']), $data)
        );
    }
}
