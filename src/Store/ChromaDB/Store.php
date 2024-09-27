<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\ChromaDB;

use Codewithkyrian\ChromaDB\Client;
use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class Store implements VectorStoreInterface
{
    public function __construct(
        private Client $client,
        private LoggerInterface $logger,
        private string $collectionName,
    ) {
    }

    public function addDocument(Document $document): void
    {
        $this->addDocuments([$document]);
    }

    public function addDocuments(array $documents): void
    {
        $ids = [];
        $vectors = [];
        $metadata = [];
        foreach ($documents as $document) {
            if (!$document->hasVector()) {
                $this->logger->warning('Document {id} does not have a vector', ['id' => $document->id]);
            }

            $ids[] = (string) $document->id;
            $vectors[] = $document->vector->getData();
            $metadata[] = $document->metadata->getArrayCopy();
        }

        $collection = $this->client->getOrCreateCollection($this->collectionName);
        $collection->add($ids, $vectors, $metadata);
    }

    public function query(Vector $vector, array $options = []): array
    {
        $collection = $this->client->getOrCreateCollection($this->collectionName);
        $queryResponse = $collection->query(
            queryEmbeddings: [$vector->getData()],
            nResults: 4,
        );

        $documents = [];
        for ($i = 0; $i < count($queryResponse->metadatas[0]); ++$i) {
            $documents[] = Document::fromVector(
                new Vector($queryResponse->embeddings[0][$i]),
                Uuid::fromString($queryResponse->ids[0][$i]),
                new Metadata($queryResponse->metadatas[0][$i]),
            );
        }

        return $documents;
    }
}
