<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Platform;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\OpenAI\Platform;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract readonly class AbstractPlatform implements Platform
{
    public function request(string $endpoint, array $body): iterable
    {
        $response = $this->rawRequest($endpoint, $body);

        if ($body['stream'] ?? false) {
            return $this->stream($response);
        }

        try {
            return $response->toArray();
        } catch (ClientException $e) {
            dump($e->getResponse()->getContent(false));
            throw new RuntimeException('Failed to make request', 0, $e);
        }
    }

    public function multiRequest(string $endpoint, array $bodies): \Generator
    {
        $responses = [];
        foreach ($bodies as $body) {
            $responses[] = $this->rawRequest($endpoint, $body);
        }

        foreach ($responses as $response) {
            yield $response->toArray();
        }
    }

    private function stream(ResponseInterface $response): \Generator
    {
        foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
            if (!$chunk instanceof ServerSentEvent || '[DONE]' === $chunk->getData()) {
                continue;
            }

            yield $chunk->getArrayData();
        }
    }

    /**
     * @param array<string, mixed> $body
     */
    abstract protected function rawRequest(string $endpoint, array $body): ResponseInterface;
}
