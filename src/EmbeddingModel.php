<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Vector;

interface EmbeddingModel
{
    public function create(string $text): Vector;

    /**
     * @param list<string> $texts
     *
     * @return list<Vector>
     */
    public function multiCreate(array $texts): array;
}
