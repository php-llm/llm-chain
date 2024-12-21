<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Response\ToolCall;

final readonly class ToolCallMessage extends Message
{
    public function __construct(
        public ToolCall $toolCall,
        public string $content,
    ) {
        parent::__construct(Role::ToolCall);
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
