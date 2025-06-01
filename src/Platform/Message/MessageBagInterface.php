<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
interface MessageBagInterface extends \Countable
{
    public function add(MessageInterface $message): void;

    /**
     * @return list<MessageInterface>
     */
    public function getMessages(): array;

    public function getSystemMessage(): ?SystemMessage;

    public function with(MessageInterface $message): self;

    public function merge(self $messageBag): self;

    public function withoutSystemMessage(): self;

    public function prepend(MessageInterface $message): self;

    public function containsAudio(): bool;

    public function containsImage(): bool;
}
