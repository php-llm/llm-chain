<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Response\ToolCall;

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
}
