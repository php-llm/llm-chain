<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AzureClient implements OpenAIClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $resource,
        private string $deployment,
        private string $apiVersion,
        private string $key,
    ) {
    }

    public function request(string $endpoint, array $body): array
    {
        $url = sprintf('https://%s.openai.azure.com/openai/deployments/%s/%s', $this->resource, $this->deployment, $endpoint);

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
