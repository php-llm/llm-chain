<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Response\ToolCall;

interface ToolboxInterface
{
    /**
     * @return Metadata[]
     */
    public function getMap(): array;

    public function execute(ToolCall $toolCall): string;
}
