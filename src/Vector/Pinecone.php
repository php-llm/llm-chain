<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Vector;

use Probots\Pinecone\Client;
use Probots\Pinecone\Resources\VectorResource;

final class Pinecone
{
    public function __construct(
        private Client $pinecone,
        private string $index,
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

        return array_map(fn (array $match) => $match['id'], $response->json()['matches']);
    }

    /**
     * @param list<array{id: string|int, values: list<float>}> $vectors
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
        return $this->pinecone->index($this->index)->vectors();
    }
}
