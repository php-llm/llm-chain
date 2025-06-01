<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

final class FillMaskResult
{
    /**
     * @param MaskFill[] $fills
     */
    public function __construct(
        public array $fills,
    ) {
    }

    /**
     * @param array<array{token: int, token_str: string, sequence: string, score: float}> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(array_map(
            fn (array $item) => new MaskFill(
                $item['token'],
                $item['token_str'],
                $item['sequence'],
                $item['score'],
            ),
            $data,
        ));
    }
}
