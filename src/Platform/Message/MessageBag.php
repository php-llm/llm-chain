<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

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

    public function count(): int
    {
        return \count($this->messages);
    }
}
