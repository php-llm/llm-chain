<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final readonly class QuestionAnsweringResult
{
    public function __construct(
        public string $answer,
        public int $startIndex,
        public int $endIndex,
        public float $score,
    ) {
    }

    /**
     * @param array{answer: string, start: int, end: int, score: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['answer'],
            $data['start'],
            $data['end'],
            $data['score'],
        );
    }
}
