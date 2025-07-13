<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Bridge\MariaDB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Store\InitializableStoreInterface;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Requires MariaDB >=11.7.
 *
 * @see https://mariadb.org/rag-with-mariadb-vector/
 *
 * @author Valtteri R <valtzu@gmail.com>
 */
final readonly class Store implements VectorStoreInterface, InitializableStoreInterface
{
    /**
     * @param string $tableName       The name of the table
     * @param string $indexName       The name of the vector search index
     * @param string $vectorFieldName The name of the field in the index that contains the vector
     */
    public function __construct(
        private \PDO $connection,
        private string $tableName,
        private string $indexName,
        private string $vectorFieldName,
    ) {
        if (!\extension_loaded('pdo')) {
            throw new \RuntimeException('For using MariaDB as retrieval vector store, the PDO extension needs to be enabled.');
        }
    }

    public static function fromPdo(\PDO $connection, string $tableName, string $indexName = 'embedding', string $vectorFieldName = 'embedding'): self
    {
        return new self($connection, $tableName, $indexName, $vectorFieldName);
    }

    /**
     * @throws DBALException
     */
    public static function fromDbal(Connection $connection, string $tableName, string $indexName = 'embedding', string $vectorFieldName = 'embedding'): self
    {
        if (!class_exists(Connection::class)) {
            throw new \RuntimeException('For using MariaDB via Doctrine as retrieval vector store, the doctrine/dbal package is required. Try running "composer require doctrine/dbal".');
        }

        $pdo = $connection->getNativeConnection();

        if (!$pdo instanceof \PDO) {
            throw new InvalidArgumentException('Only DBAL connections using PDO driver are supported.');
        }

        return self::fromPdo($pdo, $tableName, $indexName, $vectorFieldName);
    }

    public function add(VectorDocument ...$documents): void
    {
        $statement = $this->connection->prepare(
            \sprintf(
                <<<'SQL'
                    INSERT INTO %1$s (id, metadata, %2$s)
                    VALUES (:id, :metadata, VEC_FromText(:vector))
                    ON DUPLICATE KEY UPDATE metadata = :metadata, %2$s = VEC_FromText(:vector)
                    SQL,
                $this->tableName,
                $this->vectorFieldName,
            ),
        );

        foreach ($documents as $document) {
            $operation = [
                'id' => $document->id->toBinary(),
                'metadata' => json_encode($document->metadata->getArrayCopy()),
                'vector' => json_encode($document->vector->getData()),
            ];

            $statement->execute($operation);
        }
    }

    /**
     * @param array{
     *     limit?: positive-int,
     * } $options
     */
    public function query(Vector $vector, array $options = [], ?float $minScore = null): array
    {
        $statement = $this->connection->prepare(
            \sprintf(
                <<<'SQL'
                    SELECT id, VEC_ToText(%1$s) embedding, metadata, VEC_DISTANCE_EUCLIDEAN(%1$s, VEC_FromText(:embedding)) AS score
                    FROM %2$s
                    %3$s
                    ORDER BY score ASC
                    LIMIT %4$d
                    SQL,
                $this->vectorFieldName,
                $this->tableName,
                null !== $minScore ? 'WHERE VEC_DISTANCE_EUCLIDEAN(%1$s, VEC_FromText(:embedding)) >= :minScore' : '',
                $options['limit'] ?? 5,
            ),
        );

        $params = ['embedding' => json_encode($vector->getData())];

        if (null !== $minScore) {
            $params['minScore'] = $minScore;
        }

        $documents = [];

        $statement->execute($params);

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $result) {
            $documents[] = new VectorDocument(
                id: Uuid::fromBinary($result['id']),
                vector: new Vector(json_decode((string) $result['embedding'], true)),
                metadata: new Metadata(json_decode($result['metadata'] ?? '{}', true)),
                score: $result['score'],
            );
        }

        return $documents;
    }

    /**
     * @param array{dimensions?: positive-int} $options
     */
    public function initialize(array $options = []): void
    {
        if ([] !== $options && !\array_key_exists('dimensions', $options)) {
            throw new InvalidArgumentException('The only supported option is "dimensions"');
        }

        $serverVersion = $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);

        if (!str_contains((string) $serverVersion, 'MariaDB') || version_compare($serverVersion, '11.7.0') < 0) {
            throw new InvalidArgumentException('You need MariaDB >=11.7 to use this feature');
        }

        $this->connection->exec(
            \sprintf(
                <<<'SQL'
                    CREATE TABLE IF NOT EXISTS %1$s (
                        id BINARY(16) NOT NULL PRIMARY KEY,
                        metadata JSON,
                        %2$s VECTOR(%4$d) NOT NULL,
                        VECTOR INDEX %3$s (%2$s)
                    )
                    SQL,
                $this->tableName,
                $this->vectorFieldName,
                $this->indexName,
                $options['dimensions'] ?? 1536,
            ),
        );
    }
}
