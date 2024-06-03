<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Vector;

use Probots\Pinecone\Client;
use Probots\Pinecone\Resources\Data\VectorResource;

final class Pinecone
{
    public function __construct(
        private Client $pinecone,
    ) {
    }

    /**
     * @param list<float> $vector
     *
     * @return list<string|int>
     */
    public function query(array $vector): array
    {
        $response = $this->getVectors()->query($vector);

        return $response->json()['matches'];
    }

    /**
     * @param list<array{id: string|int, values: list<float>, metadata: array<string, mixed>}> $vectors
     */
    public function upsert(array $vectors): void
    {
        $this->getVectors()->upsert($vectors);
    }

    public function truncate(): void
    {
        $this->getVectors()->delete(deleteAll: true);
    }

    private function getVectors(): VectorResource
    {
        return $this->pinecone->data()->vectors();
    }
}
