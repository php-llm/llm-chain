<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Pinecone;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Probots\Pinecone\Client;
use Probots\Pinecone\Resources\Data\VectorResource;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class Store implements VectorStoreInterface
{
    /**
     * @param array<string, mixed> $filter
     */
    public function __construct(
        private Client $pinecone,
        private LoggerInterface $logger,
        private ?string $namespace = null,
        private array $filter = [],
        private int $topK = 3,
    ) {
    }

    public function addDocument(Document $document): void
    {
        $this->addDocuments([$document]);
    }

    public function addDocuments(array $documents): void
    {
        $vectors = [];
        foreach ($documents as $document) {
            if (!$document->hasVector()) {
                $this->logger->warning('Document {id} does not have a vector', ['id' => $document->id]);
                continue;
            }

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
            $documents[] = Document::fromVector(
                new Vector($match['values']),
                Uuid::fromString($match['id']),
                new Metadata($match['metadata']),
            );
        }

        return $documents;
    }

    private function getVectors(): VectorResource
    {
        return $this->pinecone->data()->vectors();
    }
}
