<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use Symfony\Component\Uid\Uuid;

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
     * Get all messages that come after a message with the specified ID.
     * If the ID is not found, returns all messages.
     *
     * @return list<MessageInterface>
     */
    public function messagesAfterId(Uuid $id): array;

    /**
     * Get messages newer than (excluding) the specified ID.
     */
    public function messagesNewerThan(Uuid $id): self;

    /**
     * Find a message by its ID.
     */
    public function findById(Uuid $id): ?MessageInterface;

    /**
     * Check if a message with the specified ID exists in the bag.
     */
    public function hasMessageWithId(Uuid $id): bool;

    /**
     * Get all IDs in the message bag in order.
     *
     * @return list<Uuid>
     */
    public function getIds(): array;
}
