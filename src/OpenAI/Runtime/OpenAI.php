<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Runtime;

use PhpLlm\LlmChain\OpenAI\Runtime;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAI implements Runtime
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
    ) {
    }

    public function request(string $endpoint, array $body): array
    {
        $url = sprintf('https://api.openai.com/v1/%s', $endpoint);

        $response = $this->httpClient->request('POST', $url, [
            'auth_bearer' => $this->apiKey,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($body),
        ]);

        return $response->toArray();
    }
}
