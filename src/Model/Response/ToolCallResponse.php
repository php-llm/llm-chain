<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

final readonly class ToolCallResponse implements ResponseInterface
{
    /**
     * @var ToolCall[]
     */
    private array $toolCalls;

    public function __construct(ToolCall ...$toolCalls)
    {
        if (0 === count($toolCalls)) {
            throw new InvalidArgumentException('Response must have at least one tool call.');
        }

        $this->toolCalls = $toolCalls;
    }

    /**
     * @return ToolCall[]
     */
    public function getContent(): array
    {
        return $this->toolCalls;
    }
}
