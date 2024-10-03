<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Platform;

use PhpLlm\LlmChain\OpenAI\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class Azure extends AbstractPlatform implements Platform
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $deployment,
        private string $apiVersion,
        private string $key,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
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
