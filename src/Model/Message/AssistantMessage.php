<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Response\ToolCall;

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

    /**
     * @return array{
     *     role: Role::Assistant,
     *     content: ?string,
     *     tool_calls?: ToolCall[],
     * }
     */
    public function jsonSerialize(): array
    {
        $array = [
            'role' => Role::Assistant,
        ];

        if (null !== $this->content) {
            $array['content'] = $this->content;
        }

        if ($this->hasToolCalls()) {
            $array['tool_calls'] = $this->toolCalls;
        }

        return $array;
    }
}
