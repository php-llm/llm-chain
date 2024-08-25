<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Runtime;

use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractRuntime
{
    public function request(string $endpoint, array $body): array
    {
        return $this->rawRequest($endpoint, $body)->toArray();
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
