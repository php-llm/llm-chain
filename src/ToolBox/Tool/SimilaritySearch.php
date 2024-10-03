<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox\Tool;

use PhpLlm\LlmChain\Document\VectorDocument;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;

#[AsTool('similarity_search', description: 'Searches for documents similar to a query or sentence.')]
final class SimilaritySearch
{
    /**
     * @var VectorDocument[]
     */
    public array $usedDocuments = [];

    public function __construct(
        private readonly EmbeddingsModel $embeddings,
        private readonly VectorStoreInterface $vectorStore,
    ) {
    }

    /**
     * @param string $searchTerm string used for similarity search
     */
    public function __invoke(string $searchTerm): string
    {
        $vector = $this->embeddings->create($searchTerm);
        $this->usedDocuments = $this->vectorStore->query($vector);

        if (0 === count($this->usedDocuments)) {
            return 'No results found';
        }

        $result = 'Found documents with following information:'.PHP_EOL;
        foreach ($this->usedDocuments as $document) {
            $result .= json_encode($document->metadata);
        }

        return $result;
    }
}
