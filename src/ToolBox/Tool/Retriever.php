<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\AsTool;
use PhpLlm\LlmChain\Vector\RetrieverInterface;

#[AsTool('retriever', description: 'Retrieves more information based on a search.')]
final class Retriever
{
    public function __construct(
        private RetrieverInterface $retriever,
    ) {
    }

    /**
     * @param string $searchTerm text used for embedding and similarity search
     */
    public function __invoke(string $searchTerm): string
    {
        return $this->retriever->enrich($searchTerm);
    }
}
