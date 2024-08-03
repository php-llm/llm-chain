<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\MessageBag;

interface ChatInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function call(MessageBag $messages, array $options = []): array;
}
