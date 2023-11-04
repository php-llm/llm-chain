<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\OpenAI;

final class Embeddings
{
    public function __construct(
        private OpenAIClient $client,
        private string $model = 'text-embedding-ada-002',
    ) {
    }

    /**
     * @return list<float>
     */
    public function create(string $text): array
    {
        $body = [
            'model' => $this->model,
            'input' => $text,
        ];

        $response = $this->client->request('embeddings', $body);

        return $response['data'][0]['embedding'];
    }
}
