<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Platform;

use PhpLlm\LlmChain\OpenAI\Platform;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class Azure extends AbstractPlatform implements Platform
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
        private readonly string $deployment,
        private readonly string $apiVersion,
        private readonly string $key,
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
