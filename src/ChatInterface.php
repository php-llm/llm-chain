<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\UserMessage;

interface ChatInterface
{
    public function start(MessageBag $messages): void;

    public function submit(UserMessage $message): AssistantMessage;
}
