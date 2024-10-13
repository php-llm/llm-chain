<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate;

use Symfony\Component\Clock\ClockInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class Client
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ClockInterface $clock,
        #[\SensitiveParameter] private string $apiKey,
    ) {
    }

    /**
     * @param string               $model The model name on Replicate, e.g. "meta/meta-llama-3.1-405b-instruct"
     * @param array<string, mixed> $body
     */
    public function request(string $model, string $endpoint, array $body): ResponseInterface
    {
        $url = sprintf('https://api.replicate.com/v1/models/%s/%s', $model, $endpoint);

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'auth_bearer' => $this->apiKey,
            'json' => ['input' => $body],
        ]);
        $data = $response->toArray();

        while (!in_array($data['status'], ['succeeded', 'failed', 'canceled'], true)) {
            $this->clock->sleep(1); // we need to wait until the prediction is ready

            $response = $this->getResponse($data['id']);
            $data = $response->toArray();
        }

        return $response;
    }

    private function getResponse(string $id): ResponseInterface
    {
        $url = sprintf('https://api.replicate.com/v1/predictions/%s', $id);

        return $this->httpClient->request('GET', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'auth_bearer' => $this->apiKey,
        ]);
    }
}
