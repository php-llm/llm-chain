<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

/**
 * @template-extends \ArrayObject<int, Message>
 *
 * @phpstan-type MessageBagData array<int, array{
 *      role: 'system'|'assistant'|'user'|'function',
 *      content: ?string,
 *      function_call?: array{name: string, arguments: string},
 *      name?: string
 *  }>
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
     * @return MessageBagData
     */
    public function toArray(): array
    {
        return array_map(
            function (Message $message) {
                $array = [
                    'role' => $message->role->value,
                ];

                if (null !== $message->content) {
                    $array['content'] = $message->content;
                }

                if (null !== $message->hasToolCalls() && Role::ToolCall === $message->role) {
                    $array['tool_call_id'] = $message->toolCalls[0]->id;
                }

                if (null !== $message->hasToolCalls() && Role::Assistant === $message->role) {
                    $array['tool_calls'] = $message->toolCalls;
                }

                return $array;
            },
            $this->getArrayCopy(),
        );
    }

    /**
     * @return MessageBagData
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
