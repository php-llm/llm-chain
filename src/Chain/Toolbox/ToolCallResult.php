<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Model\Response\ToolCall;

final readonly class ToolCallResult
{
    public function __construct(
        public ToolCall $toolCall,
        public mixed $result,
    ) {
    }
}
