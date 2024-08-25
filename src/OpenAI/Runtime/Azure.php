<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Runtime;

use PhpLlm\LlmChain\OpenAI\Runtime;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class Azure extends AbstractRuntime implements Runtime
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $deployment,
        private string $apiVersion,
        private string $key,
    ) {
    }

    protected function rawRequest(string $endpoint, array $body): ResponseInterface
    {
        $url = sprintf('https://%s/openai/deployments/%s/%s', $this->baseUrl, $this->deployment, $endpoint);

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->key,
                'Content-Type' => 'application/json',
            ],
            'query' => ['api-version' => $this->apiVersion],
            'json' => $body,
        ]);
    }
}
