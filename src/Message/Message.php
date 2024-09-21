<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Response\ToolCall;

final class Message
{
    /**
     * @param ?ToolCall[] $toolCalls
     */
    public function __construct(
        public ?string $content,
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

    public static function ofUser(string $content): self
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

    public function hasToolCalls(): bool
    {
        return null !== $this->toolCalls && 0 !== count($this->toolCalls);
    }
}
