<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\OpenAI;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAIClient
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
    public function request(string $endpoint, array $body): array
    {
        $url = sprintf('https://api.openai.com/v1/%s', $endpoint);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'auth_bearer' => $this->apiKey,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($body),
            ]);

            return $response->toArray();
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            var_dump($response->getContent(false));

            throw $exception;
        }
    }
}
