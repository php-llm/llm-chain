<?php

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @author Valtteri R <valtzu@gmail.com>
 */
interface ToolCallArgumentResolverInterface
{
    /**
     * @return array<string, mixed>
     */
    public function resolveArguments(object $tool, Tool $metadata, ToolCall $toolCall): array;
}
