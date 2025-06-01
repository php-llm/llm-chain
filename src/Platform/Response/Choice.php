<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Choice
{
    /**
     * @param ToolCall[] $toolCalls
     */
    public function __construct(
        private ?string $content = null,
        private array $toolCalls = [],
    ) {
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function hasContent(): bool
    {
        return null !== $this->content;
    }

    /**
     * @return ToolCall[]
     */
    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }

    public function hasToolCall(): bool
    {
        return 0 !== \count($this->toolCalls);
    }
}
