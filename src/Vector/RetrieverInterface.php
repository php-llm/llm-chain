<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Vector;

interface RetrieverInterface
{
    /**
     * @param string $search text used for embedding and similarity search
     */
    public function enrich(string $search): string;
}
