<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Voyage;

interface Platform
{
    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(array $body): array;
}
