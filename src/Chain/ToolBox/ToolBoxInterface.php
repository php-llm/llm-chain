<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Model\Response\ToolCall;

interface ToolBoxInterface
{
    /**
     * @return Metadata[]
     */
    public function getMap(): array;

    public function execute(ToolCall $toolCall): string;
}
