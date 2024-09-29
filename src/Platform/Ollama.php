<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Ollama
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $hostUrl,
    ) {
    }

    /**
     * @param string               $model The model name on Replicate, e.g. "meta/meta-llama-3.1-405b-instruct"
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(string $model, string $endpoint, array $body): array
    {
        $url = sprintf('%s/api/%s', $this->hostUrl, $endpoint);

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => array_merge($body, [
                'model' => $model,
            ]),
        ]);

        return dump($response->toArray());
    }
}
