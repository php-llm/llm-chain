<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

/**
 * @template-extends \ArrayObject<int, Message>
 */
final class MessageBag extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Message ...$messages)
    {
        parent::__construct(array_values($messages));
    }

    public function getSystemMessage(): ?Message
    {
        foreach ($this as $message) {
            if (Role::System === $message->role) {
                return $message;
            }
        }

        return null;
    }

    public function with(Message $message): self
    {
        $messages = clone $this;
        $messages->append($message);

        return $messages;
    }

    public function withoutSystemMessage(): self
    {
        $messages = clone $this;
        $messages->exchangeArray(
            array_values(array_filter($messages->getArrayCopy(), fn (Message $message) => !$message->isSystem()))
        );

        return $messages;
    }

    public function prepend(Message $message): self
    {
        $messages = clone $this;
        $messages->exchangeArray(array_merge([$message], $messages->getArrayCopy()));

        return $messages;
    }

    /**
     * @return Message[]
     */
    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
