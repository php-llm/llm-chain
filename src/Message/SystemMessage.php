<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

final readonly class SystemMessage implements Message
{
    use HasMetadata;
    use HasRole;

    public function __construct(
        public string $content,
    ) {
        $this->metadata = new Metadata();
        $this->role = Role::System;
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
