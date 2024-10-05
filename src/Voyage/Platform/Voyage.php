<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Voyage\Platform;

use PhpLlm\LlmChain\Voyage\Platform;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Voyage implements Platform
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
    ) {
    }

    public function request(array $body): array
    {
        $response = $this->httpClient->request('POST', 'https://api.voyageai.com/v1/embeddings', [
            'auth_bearer' => $this->apiKey,
            'json' => $body,
        ]);

        return $response->toArray();
    }
}
