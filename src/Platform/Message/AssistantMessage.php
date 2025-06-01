<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Response\ToolCall;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class AssistantMessage implements MessageInterface
{
    /**
     * @param ?ToolCall[] $toolCalls
     */
    public function __construct(
        public ?string $content = null,
        public ?array $toolCalls = null,
    ) {
    }

    public function getRole(): Role
    {
        return Role::Assistant;
    }

    public function hasToolCalls(): bool
    {
        return null !== $this->toolCalls && 0 !== \count($this->toolCalls);
    }
}
