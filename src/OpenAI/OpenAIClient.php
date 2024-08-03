<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAIClient implements OpenAIClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
    ) {
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
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
