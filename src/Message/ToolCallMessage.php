<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Response\ToolCall;

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
