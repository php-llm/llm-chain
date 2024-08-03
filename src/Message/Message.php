<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

final class Message
{
    /**
     * @param array{name: string, arguments: string} $functionCall
     */
    public function __construct(
        public ?string $content,
        public Role $role,
        public ?array $functionCall = null,
        public ?string $name = null,
    ) {
    }

    public static function forSystem(string $content): self
    {
        return new self($content, Role::System);
    }

    /**
     * @param array{name: string, arguments: string} $functionCall
     */
    public static function ofAssistant(?string $content = null, ?array $functionCall = null): self
    {
        return new self($content, Role::Assistant, $functionCall);
    }

    public static function ofUser(string $content): self
    {
        return new self($content, Role::User);
    }

    public static function ofFunctionCall(string $name, string $content): self
    {
        return new self($content, Role::FunctionCall, null, $name);
    }
}
