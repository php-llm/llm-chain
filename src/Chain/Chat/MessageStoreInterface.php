<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Chat;

use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;

interface MessageStoreInterface
{
    public function save(MessageBagInterface $messages): void;

    public function load(): MessageBagInterface;

    public function clear(): void;
}
