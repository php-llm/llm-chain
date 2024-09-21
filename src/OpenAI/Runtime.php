<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI;

interface Runtime
{
    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(string $endpoint, array $body): array;

    /**
     * @param array<array<string, mixed>> $bodies
     *
     * @return \Generator<array<string, mixed>>
     */
    public function multiRequest(string $endpoint, array $bodies): \Generator;
}
