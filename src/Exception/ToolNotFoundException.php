<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Exception;

use PhpLlm\LlmChain\Model\Response\ToolCall;

final class ToolNotFoundException extends RuntimeException
{
    public static function forToolCall(ToolCall $toolCall): self
    {
        return new self(sprintf('Tool not found for call: %s', $toolCall->name));
    }
}
