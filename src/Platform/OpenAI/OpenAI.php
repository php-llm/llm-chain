<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\OpenAI;

use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class OpenAI extends AbstractPlatform implements Platform
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    protected function rawRequest(string $endpoint, array $body): ResponseInterface
    {
        $url = sprintf('https://api.openai.com/v1/%s', $endpoint);

        return $this->httpClient->request('POST', $url, [
            'auth_bearer' => $this->apiKey,
            'json' => $body,
        ]);
    }
}
