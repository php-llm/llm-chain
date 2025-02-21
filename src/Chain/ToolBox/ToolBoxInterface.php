<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Model\Response\ToolCall;

interface ToolBoxInterface
{
    /**
     * @return Metadata[]
     */
    public function getMap(): array;

    /**
     * @throws ToolExecutionException if the tool execution fails
     * @throws ToolNotFoundException  if the tool is not found
     */
    public function execute(ToolCall $toolCall): mixed;
}
