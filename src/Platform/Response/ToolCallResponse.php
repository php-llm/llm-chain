<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;

final class ToolCallResponse extends BaseResponse
{
    /**
     * @var ToolCall[]
     */
    private readonly array $toolCalls;

    public function __construct(ToolCall ...$toolCalls)
    {
        if (0 === \count($toolCalls)) {
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
