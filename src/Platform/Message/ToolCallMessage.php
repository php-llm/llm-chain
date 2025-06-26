<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Response\ToolCall;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class ToolCallMessage implements MessageInterface
{
    public function __construct(
        public ToolCall $toolCall,
        public string $content,
    ) {
    }

    public function getRole(): Role
    {
        return Role::ToolCall;
    }

    public function getUid(): string
    {
        // Generate deterministic UID based on tool call and content
        $toolCallData = sprintf('%s:%s:%s', $this->toolCall->id, $this->toolCall->name, serialize($this->toolCall->arguments));
        $data = sprintf('toolcall:%s:%s', $toolCallData, $this->content);
        
        return hash('sha256', $data);
    }
}
