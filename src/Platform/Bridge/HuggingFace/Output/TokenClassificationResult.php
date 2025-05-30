<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final class TokenClassificationResult
{
    /**
     * @param Token[] $tokens
     */
    public function __construct(
        public array $tokens,
    ) {
    }

    /**
     * @param array<array{entity_group: string, score: float, word: string, start: int, end: int}> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(array_map(
            fn (array $item) => new Token(
                $item['entity_group'],
                $item['score'],
                $item['word'],
                $item['start'],
                $item['end'],
            ),
            $data,
        ));
    }
}
