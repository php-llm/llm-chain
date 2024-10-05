<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Response\ToolCall;

final readonly class ToolCallMessage implements Message
{
    use HasMetadata;
    use HasRole;

    public function __construct(
        public ToolCall $toolCall,
        public string $content,
    ) {
        $this->metadata = new Metadata();
        $this->role = Role::ToolCall;
    }

    /**
     * @return array{
     *     role: Role::ToolCall,
     *     content: string,
     *     tool_call_id: string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'role' => Role::ToolCall,
            'content' => $this->content,
            'tool_call_id' => $this->toolCall->id,
        ];
    }
}
