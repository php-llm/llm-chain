<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

trait HasRole
{
    private readonly Role $role;

    public function getRole(): Role
    {
        return $this->role;
    }

    public function isSystemMessage(): bool
    {
        return Role::System === $this->role;
    }

    public function isAssistantMessage(): bool
    {
        return Role::Assistant === $this->role;
    }

    public function isUserMessage(): bool
    {
        return Role::User === $this->role;
    }

    public function isToolCallMessage(): bool
    {
        return Role::ToolCall === $this->role;
    }
}
