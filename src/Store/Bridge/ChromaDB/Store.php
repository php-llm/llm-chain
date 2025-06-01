<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Bridge\ChromaDB;

use Codewithkyrian\ChromaDB\Client;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Store implements VectorStoreInterface
{
    public function __construct(
        private Client $client,
        private string $collectionName,
    ) {
    }

    public function add(VectorDocument ...$documents): void
    {
        $ids = [];
        $vectors = [];
        $metadata = [];
        foreach ($documents as $document) {
            $ids[] = (string) $document->id;
            $vectors[] = $document->vector->getData();
            $metadata[] = $document->metadata->getArrayCopy();
        }

        $collection = $this->client->getOrCreateCollection($this->collectionName);
        $collection->add($ids, $vectors, $metadata);
    }

    public function query(Vector $vector, array $options = [], ?float $minScore = null): array
    {
        $collection = $this->client->getOrCreateCollection($this->collectionName);
        $queryResponse = $collection->query(
            queryEmbeddings: [$vector->getData()],
            nResults: 4,
        );

        $documents = [];
        for ($i = 0; $i < \count($queryResponse->metadatas[0]); ++$i) {
            $documents[] = new VectorDocument(
                id: Uuid::fromString($queryResponse->ids[0][$i]),
                vector: new Vector($queryResponse->embeddings[0][$i]),
                metadata: new Metadata($queryResponse->metadatas[0][$i]),
            );
        }

        return $documents;
    }
}
