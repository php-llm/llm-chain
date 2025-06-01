<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ClassificationResult
{
    /**
     * @param Classification[] $classifications
     */
    public function __construct(
        public array $classifications,
    ) {
    }

    /**
     * @param array<array{label: string, score: float}> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            array_map(fn (array $item) => new Classification($item['label'], $item['score']), $data)
        );
    }
}
