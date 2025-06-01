<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class SentenceSimilarityResult
{
    /**
     * @param array<float> $similarities
     */
    public function __construct(
        public array $similarities,
    ) {
    }

    /**
     * @param array<float> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
