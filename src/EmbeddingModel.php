<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

interface EmbeddingModel
{
    /**
     * @return list<float>
     */
    public function create(string $text): array;
}
