<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\MySQL;

use PDO;
use PDOException;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Document\VectorDocument;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Symfony\Component\Uid\Uuid;

final readonly class Store implements VectorStoreInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private PDO $connection,
        private string $tableName = 'vector_documents',
        private string $vectorColumnName = 'vector_data',
        private string $metadataColumnName = 'metadata',
        private array $options = [],
        private int $dimensions = 1536,
        private int $limit = 3,
    ) {
        $this->ensureTableExists();
    }

    public function add(VectorDocument ...$documents): void
    {
        if ([] === $documents) {
            return;
        }

        $sql = sprintf(
            'INSERT INTO %s (id, %s, %s) VALUES (?, JSON_ARRAY_PACK(?), ?)',
            $this->tableName,
            $this->vectorColumnName,
            $this->metadataColumnName
        );

        $statement = $this->connection->prepare($sql);
        $this->connection->beginTransaction();

        try {
            foreach ($documents as $document) {
                $statement->execute([
                    (string) $document->id,
                    json_encode($document->vector->getData()),
                    json_encode($document->metadata->getArrayCopy()),
                ]);
            }
            $this->connection->commit();
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function query(Vector $vector, array $options = [], ?float $minScore = null): array
    {
        $limit = $options['limit'] ?? $this->limit;
        $minScoreCondition = $minScore !== null ? "HAVING score >= $minScore" : '';

        $sql = sprintf(
            'SELECT 
                id,
                %s as vector_data,
                %s as metadata,
                VECTOR_COSINE_DISTANCE(%s, JSON_ARRAY_PACK(?)) as score
            FROM %s
            %s
            ORDER BY score
            LIMIT %d',
            $this->vectorColumnName,
            $this->metadataColumnName,
            $this->vectorColumnName,
            $this->tableName,
            $minScoreCondition,
            $limit
        );

        $statement = $this->connection->prepare($sql);
        $statement->execute([json_encode($vector->getData())]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $documents = [];
        foreach ($results as $result) {
            $vectorData = json_decode($result['vector_data'], true);
            $metadataArray = json_decode($result['metadata'], true);
            
            // Die Cosine-Distanz in eine Ähnlichkeits-Score umwandeln (1 - Distanz)
            // MySQL gibt die Distanz zurück, wir müssen sie in einen Ähnlichkeitswert umwandeln
            $similarityScore = 1 - $result['score'];
            
            $documents[] = new VectorDocument(
                id: Uuid::fromString($result['id']),
                vector: new Vector($vectorData),
                metadata: new Metadata($metadataArray),
                score: $similarityScore,
            );
        }

        return $documents;
    }

    private function ensureTableExists(): void
    {
        $tableExistsQuery = "SHOW TABLES LIKE '$this->tableName'";
        $tableExists = $this->connection->query($tableExistsQuery)->rowCount() > 0;

        if (!$tableExists) {
            $sql = sprintf(
                'CREATE TABLE %s (
                    id VARCHAR(36) PRIMARY KEY,
                    %s JSON NOT NULL,
                    %s JSON,
                    VECTOR USING %s(%d)
                )',
                $this->tableName,
                $this->vectorColumnName,
                $this->metadataColumnName,
                $this->vectorColumnName,
                $this->dimensions
            );
            $this->connection->exec($sql);
        }
    }
}