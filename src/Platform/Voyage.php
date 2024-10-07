<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Voyage
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
    ) {
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(array $body): array
    {
        $response = $this->httpClient->request('POST', 'https://api.voyageai.com/v1/embeddings', [
            'headers' => ['Content-Type' => 'application/json'],
            'auth_bearer' => $this->apiKey,
            'json' => $body,
        ]);

        return $response->toArray();
    }
}
