<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Chat\MessageStore;

use PhpLlm\LlmChain\Chain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;

final class InMemoryStore implements MessageStoreInterface
{
    private MessageBagInterface $messages;

    public function save(MessageBagInterface $messages): void
    {
        $this->messages = $messages;
    }

    public function load(): MessageBagInterface
    {
        return $this->messages ?? new MessageBag();
    }

    public function clear(): void
    {
        $this->messages = new MessageBag();
    }
}
