<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Exception;

use PhpLlm\LlmChain\Model\Response\ToolCall;

final class ToolNotFoundException extends \RuntimeException implements ExceptionInterface
{
    public ?ToolCall $toolCall = null;

    public static function notFoundForToolCall(ToolCall $toolCall): self
    {
        $exception = new self(sprintf('Tool not found for call: %s.', $toolCall->name));
        $exception->toolCall = $toolCall;

        return $exception;
    }
}
