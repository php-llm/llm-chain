<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain;

use SymfonyLlm\LlmChain\Message\Message;

interface RetrieverInterface
{
    public function enrich(Message $message): Message;
}
