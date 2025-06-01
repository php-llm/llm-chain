<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface ToolboxInterface
{
    /**
     * @return Tool[]
     */
    public function getTools(): array;

    /**
     * @throws ToolExecutionException if the tool execution fails
     * @throws ToolNotFoundException  if the tool is not found
     */
    public function execute(ToolCall $toolCall): mixed;
}
