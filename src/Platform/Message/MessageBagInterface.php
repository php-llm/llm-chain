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

    /**
     * Get all messages that come after a message with the specified UID.
     * If the UID is not found, returns all messages.
     *
     * @return list<MessageInterface>
     */
    public function messagesAfterUid(string $uid): array;

    /**
     * Get messages newer than (excluding) the specified UID.
     */
    public function messagesNewerThan(string $uid): self;

    /**
     * Find a message by its UID.
     */
    public function findByUid(string $uid): ?MessageInterface;

    /**
     * Check if a message with the specified UID exists in the bag.
     */
    public function hasMessageWithUid(string $uid): bool;

    /**
     * Get all UIDs in the message bag in order.
     *
     * @return list<string>
     */
    public function getUids(): array;
}
