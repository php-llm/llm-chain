<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

interface LlmChainInterface
{
    public function call(Message $message, MessageBag $messages): string;
}
