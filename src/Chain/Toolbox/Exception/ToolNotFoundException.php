<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Exception;

use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Tool\ExecutionReference;

final class ToolNotFoundException extends \RuntimeException implements ExceptionInterface
{
    public ?ToolCall $toolCall = null;

    public static function notFoundForToolCall(ToolCall $toolCall): self
    {
        $exception = new self(\sprintf('Tool not found for call: %s.', $toolCall->name));
        $exception->toolCall = $toolCall;

        return $exception;
    }

    public static function notFoundForReference(ExecutionReference $reference): self
    {
        return new self(\sprintf('Tool not found for reference: %s::%s.', $reference->class, $reference->method));
    }
}
