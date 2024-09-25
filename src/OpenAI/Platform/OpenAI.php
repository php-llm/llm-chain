<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Platform;

use PhpLlm\LlmChain\OpenAI\Platform;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class OpenAI extends AbstractPlatform implements Platform
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {
    }

    protected function rawRequest(string $endpoint, array $body): ResponseInterface
    {
        $url = sprintf('https://api.openai.com/v1/%s', $endpoint);

        return $this->httpClient->request('POST', $url, [
            'auth_bearer' => $this->apiKey,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($body),
        ]);
    }
}