<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\OpenAI;

use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class Azure extends AbstractPlatform implements Platform
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $deployment,
        private string $apiVersion,
        #[\SensitiveParameter] private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        Assert::notStartsWith($baseUrl, 'http://', 'The base URL must not contain the protocol.');
        Assert::notStartsWith($baseUrl, 'https://', 'The base URL must not contain the protocol.');
        Assert::stringNotEmpty($deployment, 'The deployment must not be empty.');
        Assert::stringNotEmpty($apiVersion, 'The API version must not be empty.');
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
    }

    protected function rawRequest(string $endpoint, array $body): ResponseInterface
    {
        $url = sprintf('https://%s/openai/deployments/%s/%s', $this->baseUrl, $this->deployment, $endpoint);

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->apiKey,
            ],
            'query' => ['api-version' => $this->apiVersion],
            'json' => $body,
        ]);
    }
}
