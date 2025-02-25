<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chat\MessageStore;

use PhpLlm\LlmChain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Model\Message\MessageBag;

final class InMemoryStore implements MessageStoreInterface
{
    private MessageBag $messages;

    public function save(MessageBag $messages): void
    {
        $this->messages = $messages;
    }

    public function load(): MessageBag
    {
        return $this->messages ?? new MessageBag();
    }

    public function clear(): void
    {
        $this->messages = new MessageBag();
    }
}
