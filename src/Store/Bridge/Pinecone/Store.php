<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Bridge\Pinecone;

use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Probots\Pinecone\Client;
use Probots\Pinecone\Resources\Data\VectorResource;
use Symfony\Component\Uid\Uuid;

final readonly class Store implements VectorStoreInterface
{
    /**
     * @param array<string, mixed> $filter
     */
    public function __construct(
        private Client $pinecone,
        private ?string $namespace = null,
        private array $filter = [],
        private int $topK = 3,
    ) {
    }

    public function add(VectorDocument ...$documents): void
    {
        $vectors = [];
        foreach ($documents as $document) {
            $vectors[] = [
                'id' => (string) $document->id,
                'values' => $document->vector->getData(),
                'metadata' => $document->metadata->getArrayCopy(),
            ];
        }

        if ([] === $vectors) {
            return;
        }

        $this->getVectors()->upsert($vectors, $this->namespace);
    }

    public function query(Vector $vector, array $options = [], ?float $minScore = null): array
    {
        $response = $this->getVectors()->query(
            vector: $vector->getData(),
            namespace: $options['namespace'] ?? $this->namespace,
            filter: $options['filter'] ?? $this->filter,
            topK: $options['topK'] ?? $this->topK,
            includeValues: true,
        );

        $documents = [];
        foreach ($response->json()['matches'] as $match) {
            $documents[] = new VectorDocument(
                id: Uuid::fromString($match['id']),
                vector: new Vector($match['values']),
                metadata: new Metadata($match['metadata']),
                score: $match['score'],
            );
        }

        return $documents;
    }

    private function getVectors(): VectorResource
    {
        return $this->pinecone->data()->vectors();
    }
}
