<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI;

interface Platform
{
    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(string $endpoint, array $body): iterable;

    /**
     * @param array<array<string, mixed>> $bodies
     *
     * @return \Generator<array<string, mixed>>
     */
    public function multiRequest(string $endpoint, array $bodies): \Generator;
}
