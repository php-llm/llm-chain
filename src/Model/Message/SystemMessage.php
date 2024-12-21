<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

final readonly class SystemMessage extends Message
{
    public function __construct(public string $content)
    {
        parent::__construct(Role::System);
    }

    /**
     * @return array{
     *     role: Role::System,
     *     content: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'role' => Role::System,
            'content' => $this->content,
        ];
    }
}
