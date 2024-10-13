<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

/**
 * @template-extends \ArrayObject<int, MessageInterface>
 */
final class MessageBag extends \ArrayObject implements \JsonSerializable
{
    public function __construct(MessageInterface ...$messages)
    {
        parent::__construct(array_values($messages));
    }

    public function getSystemMessage(): ?SystemMessage
    {
        foreach ($this as $message) {
            if ($message instanceof SystemMessage) {
                return $message;
            }
        }

        return null;
    }

    public function with(MessageInterface $message): self
    {
        $messages = clone $this;
        $messages->append($message);

        return $messages;
    }

    public function merge(MessageBag $messageBag): self
    {
        $messages = clone $this;
        $messages->exchangeArray(array_merge($messages->getArrayCopy(), $messageBag->getArrayCopy()));

        return $messages;
    }

    public function withoutSystemMessage(): self
    {
        $messages = clone $this;
        $messages->exchangeArray(
            array_values(array_filter(
                $messages->getArrayCopy(),
                static fn (MessageInterface $message) => !$message instanceof SystemMessage,
            ))
        );

        return $messages;
    }

    public function prepend(MessageInterface $message): self
    {
        $messages = clone $this;
        $messages->exchangeArray(array_merge([$message], $messages->getArrayCopy()));

        return $messages;
    }

    public function containsImage(): bool
    {
        foreach ($this as $message) {
            if ($message instanceof UserMessage && $message->hasImageContent()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return MessageInterface[]
     */
    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
