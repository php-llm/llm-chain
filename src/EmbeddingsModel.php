<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Vector;

interface EmbeddingsModel
{
    /**
     * @param array<string, mixed> $options
     */
    public function create(string $text, array $options = []): Vector;

    /**
     * @param list<string>         $texts
     * @param array<string, mixed> $options
     *
     * @return Vector[]
     */
    public function multiCreate(array $texts, array $options = []): array;
}
