<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Exception;

use PhpLlm\LlmChain\Model\Response\ToolCall;

final class ToolBoxException extends RuntimeException
{
    public ?ToolCall $toolCall = null;

    public static function notFoundForToolCall(ToolCall $toolCall): self
    {
        $exception = new self(sprintf('Tool not found for call: %s.', $toolCall->name));
        $exception->toolCall = $toolCall;

        return $exception;
    }

    public static function invalidMethod(string $toolClass, string $methodName): self
    {
        return new self(sprintf('Method "%s" not found in tool "%s".', $methodName, $toolClass));
    }

    public static function executionFailed(ToolCall $toolCall, \Throwable $previous): self
    {
        $exception = new self(sprintf('Execution of tool "%s" failed with error: %s', $toolCall->name, $previous->getMessage()), previous: $previous);
        $exception->toolCall = $toolCall;

        return $exception;
    }
}
