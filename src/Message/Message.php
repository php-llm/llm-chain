<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Response\ToolCall;

final readonly class Message implements \JsonSerializable
{
    /**
     * @param ?ToolCall[] $toolCalls
     */
    public function __construct(
        public string|ImageUrl|null $content,
        public Role $role,
        public ?array $toolCalls = null,
    ) {
    }

    public static function forSystem(string $content): self
    {
        return new self($content, Role::System);
    }

    /**
     * @param ?ToolCall[] $toolCalls
     */
    public static function ofAssistant(?string $content = null, ?array $toolCalls = null): self
    {
        return new self($content, Role::Assistant, $toolCalls);
    }

    public static function ofUser(string|ImageUrl $content): self
    {
        return new self($content, Role::User);
    }

    public static function ofToolCall(ToolCall $toolCall, string $content): self
    {
        return new self($content, Role::ToolCall, [$toolCall]);
    }

    public function isSystem(): bool
    {
        return Role::System === $this->role;
    }

    public function isAssistant(): bool
    {
        return Role::Assistant === $this->role;
    }

    public function isUser(): bool
    {
        return Role::User === $this->role;
    }

    public function isToolCall(): bool
    {
        return Role::ToolCall === $this->role;
    }

    public function hasToolCalls(): bool
    {
        return null !== $this->toolCalls && 0 !== count($this->toolCalls);
    }

    /**
     * @return array{
     *     role: 'system'|'assistant'|'user'|'tool',
     *     content: ?string,
     *     tool_calls?: ToolCall[],
     *     tool_call_id?: string
     * }
     */
    public function jsonSerialize(): array
    {
        $array = [
            'role' => $this->role->value,
        ];

        if (null !== $this->content) {
            $content = $this->content;

            if ($this->content instanceof ImageUrl) {
                $content = [];
                $content[] = $this->content;
            }

            $array['content'] = $content;
        }

        if ($this->hasToolCalls() && $this->isToolCall()) {
            $array['tool_call_id'] = $this->toolCalls[0]->id;
        }

        if ($this->hasToolCalls() && $this->isAssistant()) {
            $array['tool_calls'] = $this->toolCalls;
        }

        return $array;
    }
}
