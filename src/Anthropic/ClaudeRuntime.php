<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic;

interface ClaudeRuntime
{
    public function request(array $body): array;
}
