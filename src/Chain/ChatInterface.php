<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\UserMessage;

interface ChatInterface
{
    public function initiate(MessageBag $messages): void;

    public function submit(UserMessage $message): AssistantMessage;
}
