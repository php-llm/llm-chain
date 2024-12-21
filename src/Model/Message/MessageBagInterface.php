<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

interface MessageBagInterface extends \JsonSerializable
{
    public function getSystemMessage(): ?SystemMessage;

    public function with(MessageInterface $message): self;

    public function merge(MessageBagInterface $messageBag): self;

    public function withoutSystemMessage(): self;

    public function prepend(MessageInterface $message): self;

    public function containsImage(): bool;

    public function getArrayCopy(): array;
}
