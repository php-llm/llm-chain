<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Bridge\MongoDB;

use MongoDB\BSON\Binary;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\CommandException;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Store\InitializableStoreInterface;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
final readonly class Store implements VectorStoreInterface, InitializableStoreInterface
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
        private string $databaseName,
        private string $collectionName,
        private string $indexName,
        private string $vectorFieldName = 'vector',
        private bool $bulkWrite = false,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        if (!class_exists(Client::class)) {
            throw new \RuntimeException('For using MongoDB Atlas as retrieval vector store, the mongodb/mongodb package is required. Try running "composer require mongodb/mongodb".');
        }
    }

    public function add(VectorDocument ...$documents): void
    {
        $operations = [];

        foreach ($documents as $document) {
            $operation = [
                ['_id' => $this->toBinary($document->id)], // we use binary for the id, because of storage efficiency
                array_filter([
                    'metadata' => $document->metadata->getArrayCopy(),
                    $this->vectorFieldName => $document->vector->getData(),
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
     */
    public function query(Vector $vector, array $options = [], ?float $minScore = null): array
    {
        $pipeline = [
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
        ];

        if (null !== $minScore) {
            $pipeline[] = [
                '$match' => [
                    'score' => ['$gte' => $minScore],
                ],
            ];
        }

        $results = $this->getCollection()->aggregate(
            $pipeline,
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );

        $documents = [];

        foreach ($results as $result) {
            $documents[] = new VectorDocument(
                id: $this->toUuid($result['_id']),
                vector: new Vector($result[$this->vectorFieldName]),
                metadata: new Metadata($result['metadata'] ?? []),
                score: $result['score'],
            );
        }

        return $documents;
    }

    /**
     * @param array{fields?: array<mixed>} $options
     */
    public function initialize(array $options = []): void
    {
        if ([] !== $options && !\array_key_exists('fields', $options)) {
            throw new InvalidArgumentException('The only supported option is "fields"');
        }

        try {
            $this->getCollection()->createSearchIndex(
                [
                    'fields' => array_merge([
                        [
                            'numDimensions' => 1536,
                            'path' => $this->vectorFieldName,
                            'similarity' => 'euclidean',
                            'type' => 'vector',
                        ],
                    ], $options['fields'] ?? []),
                ],
                [
                    'name' => $this->indexName,
                    'type' => 'vectorSearch',
                ],
            );
        } catch (CommandException $e) {
            $this->logger->warning($e->getMessage());
        }
    }

    private function getCollection(): Collection
    {
        return $this->client->getCollection($this->databaseName, $this->collectionName);
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
