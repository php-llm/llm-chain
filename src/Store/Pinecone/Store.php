<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Pinecone;

use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Document\VectorDocument;
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

        $this->getVectors()->upsert($vectors);
    }

    public function query(Vector $vector, array $options = []): array
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
            );
        }

        return $documents;
    }

    private function getVectors(): VectorResource
    {
        return $this->pinecone->data()->vectors();
    }
}
