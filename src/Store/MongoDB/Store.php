<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\MongoDB;

use MongoDB\BSON\Binary;
use MongoDB\Client;
use MongoDB\Collection;
use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @see https://www.mongodb.com/docs/atlas/atlas-vector-search/vector-search-overview/
 *
 * For this store you need to create a separate MongoDB Atlas Search index.
 * The index needs to be created with the following settings:
 * {
 *     "fields": [
 *         {
 *             "numDimensions": 1536,
 *             "path": "vector",
 *             "similarity": "euclidean",
 *             "type": "vector"
 *         }
 *     ]
 * }
 *
 * Note, that the `path` key needs to match the $vectorFieldName.
 *
 * For the `similarity` key you can choose between `euclidean`, `cosine` and `dotProduct`.
 * {@see https://www.mongodb.com/docs/atlas/atlas-search/field-types/knn-vector/#define-the-index-for-the-fts-field-type-type}
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final readonly class Store implements VectorStoreInterface
{
    /**
     * @param string $databaseName    The name of the database
     * @param string $collectionName  The name of the collection
     * @param string $indexName       The name of the Atlas Search index
     * @param string $vectorFieldName The name of the field int the index that contains the vector
     * @param bool   $bulkWrite       Use bulk write operations
     */
    public function __construct(
        private Client $client,
        private LoggerInterface $logger,
        private string $databaseName,
        private string $collectionName,
        private string $indexName,
        private string $vectorFieldName = 'vector',
        private bool $bulkWrite = false,
    ) {
    }

    public function addDocument(Document $document): void
    {
        $this->addDocuments([$document]);
    }

    public function addDocuments(array $documents): void
    {
        $operations = [];

        foreach ($documents as $document) {
            if (!$document->hasVector()) {
                $this->logger->warning('Document {id} does not have a vector', ['id' => $document->id]);
            }

            $operation = [
                ['_id' => $this->toBinary($document->id)], // we use binary for the id, because of storage efficiency
                array_filter([
                    'metadata' => $document->metadata,
                    $this->vectorFieldName => $document->vector->getData(),
                    'text' => $document->text,
                ]),
                ['upsert' => true], // insert if not exists
            ];

            if ($this->bulkWrite) {
                $operations[] = ['replaceOne' => $operation];
                continue;
            }

            $this->getCollection()->replaceOne(...$operation);
        }

        if ($this->bulkWrite) {
            $this->getCollection()->bulkWrite($operations);
        }
    }

    /**
     * @param array{
     *     limit?: positive-int,
     *     numCandidates?: positive-int,
     *     filter?: array<mixed>
     * } $options
     *
     * @return Document[]
     */
    public function query(Vector $vector, array $options = []): array
    {
        $results = $this->getCollection()->aggregate([
            [
                '$vectorSearch' => array_merge([
                    'index' => $this->indexName,
                    'path' => $this->vectorFieldName,
                    'queryVector' => $vector->getData(),
                    'numCandidates' => 200,
                    'limit' => 5,
                ], $options),
            ],
            [
                '$addFields' => [
                    'score' => ['$meta' => 'vectorSearchScore'],
                ],
            ],
        ], ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]);

        $documents = [];

        foreach ($results as $result) {
            $documents[] = Document::fromVector(
                new Vector($result[$this->vectorFieldName]),
                $this->toUuid($result['_id']),
                new Metadata($result['metadata'] ?? []),
            );
        }

        return $documents;
    }

    private function getCollection(): Collection
    {
        return $this->client->selectCollection($this->databaseName, $this->collectionName);
    }

    private function toBinary(Uuid $uuid): Binary
    {
        return new Binary($uuid->toBinary(), Binary::TYPE_UUID);
    }

    private function toUuid(Binary $binary): Uuid
    {
        return Uuid::fromString($binary->getData());
    }
}
