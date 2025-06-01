<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Exception;

use PhpLlm\LlmChain\Platform\Response\ToolCall;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ToolExecutionException extends \RuntimeException implements ExceptionInterface
{
    public ?ToolCall $toolCall = null;

    public static function executionFailed(ToolCall $toolCall, \Throwable $previous): self
    {
        $exception = new self(\sprintf('Execution of tool "%s" failed with error: %s', $toolCall->name, $previous->getMessage()), previous: $previous);
        $exception->toolCall = $toolCall;

        return $exception;
    }
}
