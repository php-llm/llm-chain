<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final class ObjectDetectionResult
{
    /**
     * @param DetectedObject[] $objects
     */
    public function __construct(
        public array $objects,
    ) {
    }

    /**
     * @param array<array{label: string, score: float, box: array{xmin: float, ymin: float, xmax: float, ymax: float}}> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(array_map(
            fn (array $item) => new DetectedObject(
                $item['label'],
                $item['score'],
                $item['box']['xmin'],
                $item['box']['ymin'],
                $item['box']['xmax'],
                $item['box']['ymax'],
            ),
            $data,
        ));
    }
}
