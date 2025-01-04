<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Exception;

use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Model\Response\ToolCall;

final class ToolBoxException extends RuntimeException
{
    public ToolCall $toolCall;

    public static function notFoundForToolCall(ToolCall $toolCall): self
    {
        $exception = new self(sprintf('Tool not found for call: %s', $toolCall->name));
        $exception->toolCall = $toolCall;

        return $exception;
    }

    public static function invalidMethod(ToolCall $toolCall, string $toolClass, Metadata $metadata): self
    {
        $exception = new self(sprintf('Invalid method "%s" on tool "%s" for tool call "%s".', $metadata->method, $toolClass, $toolCall->name));
        $exception->toolCall = $toolCall;

        return $exception;
    }

    public static function executionFailed(ToolCall $toolCall, \Throwable $previous): self
    {
        $exception = new self(sprintf('Execution of tool "%s" failed with error: %s', $toolCall->name, $previous->getMessage()), 0, $previous);
        $exception->toolCall = $toolCall;

        return $exception;
    }
}
