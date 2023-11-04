<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain;

use SymfonyLlm\LlmChain\Message\Message;
use SymfonyLlm\LlmChain\Message\MessageBag;

interface LlmChainInterface
{
    public function call(Message $message, MessageBag $messages): string;
}
