<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI;

interface OpenAIClientInterface
{
    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(string $endpoint, array $body): array;
}
