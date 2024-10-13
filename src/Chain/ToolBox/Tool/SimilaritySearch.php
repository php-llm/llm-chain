<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Document\VectorDocument;
use PhpLlm\LlmChain\Model\EmbeddingsModel;
use PhpLlm\LlmChain\Platform;
use PhpLlm\LlmChain\Store\VectorStoreInterface;

#[AsTool('similarity_search', description: 'Searches for documents similar to a query or sentence.')]
final class SimilaritySearch
{
    /**
     * @var VectorDocument[]
     */
    public array $usedDocuments = [];

    public function __construct(
        private readonly Platform $platform,
        private readonly EmbeddingsModel $embeddings,
        private readonly VectorStoreInterface $vectorStore,
    ) {
    }

    /**
     * @param string $searchTerm string used for similarity search
     */
    public function __invoke(string $searchTerm): string
    {
        /** @var Vector[] $vectors */
        $vectors = $this->platform->request($this->embeddings, $searchTerm)->getContent();
        $this->usedDocuments = $this->vectorStore->query($vectors[0]);

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
