<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use Symfony\Component\Uid\Uuid;

/**
 * @final
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
class MessageBag implements MessageBagInterface
{
    /**
     * @var list<MessageInterface>
     */
    private array $messages;

    public function __construct(MessageInterface ...$messages)
    {
        $this->messages = array_values($messages);
    }

    public function add(MessageInterface $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return list<MessageInterface>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getSystemMessage(): ?SystemMessage
    {
        foreach ($this->messages as $message) {
            if ($message instanceof SystemMessage) {
                return $message;
            }
        }

        return null;
    }

    public function with(MessageInterface $message): self
    {
        $messages = clone $this;
        $messages->add($message);

        return $messages;
    }

    public function merge(MessageBagInterface $messageBag): self
    {
        $messages = clone $this;
        $messages->messages = array_merge($messages->messages, $messageBag->getMessages());

        return $messages;
    }

    public function withoutSystemMessage(): self
    {
        $messages = clone $this;
        $messages->messages = array_values(array_filter(
            $messages->messages,
            static fn (MessageInterface $message) => !$message instanceof SystemMessage,
        ));

        return $messages;
    }

    public function prepend(MessageInterface $message): self
    {
        $messages = clone $this;
        $messages->messages = array_merge([$message], $messages->messages);

        return $messages;
    }

    public function containsAudio(): bool
    {
        foreach ($this->messages as $message) {
            if ($message instanceof UserMessage && $message->hasAudioContent()) {
                return true;
            }
        }

        return false;
    }

    public function containsImage(): bool
    {
        foreach ($this->messages as $message) {
            if ($message instanceof UserMessage && $message->hasImageContent()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all messages that come after a message with the specified ID.
     * If the ID is not found, returns all messages.
     *
     * @return list<MessageInterface>
     */
    public function messagesAfterId(Uuid $id): array
    {
        $found = false;
        $messagesAfter = [];

        foreach ($this->messages as $message) {
            if ($found) {
                $messagesAfter[] = $message;
            } elseif ($message->getId()->equals($id)) {
                $found = true;
            }
        }

        // If ID not found, return all messages
        return $found ? $messagesAfter : $this->messages;
    }

    /**
     * Get messages newer than (excluding) the specified ID.
     *
     * @return self
     */
    public function messagesNewerThan(Uuid $id): self
    {
        $messagesAfter = $this->messagesAfterId($id);
        
        return new self(...$messagesAfter);
    }

    /**
     * Find a message by its ID.
     */
    public function findById(Uuid $id): ?MessageInterface
    {
        foreach ($this->messages as $message) {
            if ($message->getId()->equals($id)) {
                return $message;
            }
        }

        return null;
    }

    /**
     * Check if a message with the specified ID exists in the bag.
     */
    public function hasMessageWithId(Uuid $id): bool
    {
        return $this->findById($id) !== null;
    }

    /**
     * Get all IDs in the message bag in order.
     *
     * @return list<Uuid>
     */
    public function getIds(): array
    {
        return array_map(
            static fn (MessageInterface $message) => $message->getId(),
            $this->messages
        );
    }

    public function count(): int
    {
        return \count($this->messages);
    }
}
