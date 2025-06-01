<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Platform\Response\ToolCall;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ToolCallResult
{
    public function __construct(
        public ToolCall $toolCall,
        public mixed $result,
    ) {
    }
}
