<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chat;

use PhpLlm\LlmChain\Model\Message\MessageBag;

interface MessageStoreInterface
{
    public function save(MessageBag $messages): void;

    public function load(): MessageBag;

    public function clear(): void;
}
