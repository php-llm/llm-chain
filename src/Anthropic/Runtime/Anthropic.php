<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Runtime;

use PhpLlm\LlmChain\Anthropic\ClaudeRuntime;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Anthropic implements ClaudeRuntime
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
    public function request(array $body): array
    {
        $response = $this->httpClient->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ],
            'json' => $body,
        ]);

        return $response->toArray();
    }
}
