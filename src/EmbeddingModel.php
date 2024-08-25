<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Vector;

interface EmbeddingModel
{
    /**
     * @return list<float>
     */
    public function create(string $text): Vector;

    /**
     * @return list<Vector>
     */
    public function multiCreate(array $texts): array;
}
