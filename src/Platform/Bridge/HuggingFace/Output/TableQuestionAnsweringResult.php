<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class TableQuestionAnsweringResult
{
    /**
     * @param array<int, string|int> $cells
     * @param array<string>          $aggregator
     */
    public function __construct(
        public string $answer,
        public array $cells = [],
        public array $aggregator = [],
    ) {
    }

    /**
     * @param array{answer: string, cells?: array<int, string|int>, aggregator?: array<string>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['answer'],
            $data['cells'] ?? [],
            $data['aggregator'] ?? [],
        );
    }
}
