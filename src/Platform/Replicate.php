<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Replicate
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
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
        $url = sprintf('https://api.replicate.com/v1/models/%s/%s', $model, $endpoint);

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'auth_bearer' => $this->apiKey,
            'json' => ['input' => $body],
        ])->toArray();

        while (!in_array($response['status'], ['succeeded', 'failed', 'canceled'], true)) {
            sleep(1);

            $response = $this->getResponse($response['id']);
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function getResponse(string $id): array
    {
        $url = sprintf('https://api.replicate.com/v1/predictions/%s', $id);

        $response = $this->httpClient->request('GET', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'auth_bearer' => $this->apiKey,
        ]);

        return $response->toArray();
    }
}
