<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Vector\ChromaDb;

use Codewithkyrian\ChromaDB\Client;
use PhpLlm\LlmChain\OpenAI\Embeddings;
use PhpLlm\LlmChain\Vector\RetrieverInterface;

final class Retriever implements RetrieverInterface
{
    public function __construct(
        private Embeddings $embeddings,
        private Client $chromaClient,
        private string $collectionName,
    ) {
    }

    /**
     * @param string $search text used for embedding and similarity search
     */
    public function enrich(string $search): string
    {
        $vector = $this->embeddings->create($search);
        $collection = $this->chromaClient->getOrCreateCollection($this->collectionName);
        $queryResponse = $collection->query(
            queryEmbeddings: [$vector],
            nResults: 4,
        );

        if (1 === count($queryResponse->ids, COUNT_RECURSIVE)) {
            return 'No results found';
        }

        $result = '';
        foreach ($queryResponse->metadatas[0] as $metadata) {
            foreach ($metadata as $key => $value) {
                $result .= ucfirst($key).': '.$value.', ';
            }
            $result .= PHP_EOL;
        }

        return $result;
    }
}
