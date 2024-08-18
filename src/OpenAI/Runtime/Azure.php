<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Runtime;

use PhpLlm\LlmChain\OpenAI\Runtime;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Azure implements Runtime
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $deployment,
        private string $apiVersion,
        private string $key,
    ) {
    }

    public function request(string $endpoint, array $body): array
    {
        $url = sprintf('https://%s/openai/deployments/%s/%s', $this->baseUrl, $this->deployment, $endpoint);

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->key,
                'Content-Type' => 'application/json',
            ],
            'query' => ['api-version' => $this->apiVersion],
            'json' => $body,
        ]);

        return $response->toArray();
    }
}
