<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Runtime;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractRuntime
{
    public function request(string $endpoint, array $body): array
    {
        try {
            return $this->rawRequest($endpoint, $body)->toArray();
        } catch (ClientException $e) {
            dump($e->getResponse()->getContent(false));
            throw new \RuntimeException('Failed to make request', 0, $e);
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

    abstract protected function rawRequest(string $endpoint, array $body): ResponseInterface;
}
