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

namespace PhpLlm\LlmChain\Response;

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
        return 0 !== count($this->toolCalls);
    }
}
