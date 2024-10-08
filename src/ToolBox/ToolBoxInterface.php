<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Response\ToolCall;

interface ToolBoxInterface
{
    /**
     * @return Metadata[]
     */
    public function getMap(): array;

    public function execute(ToolCall $toolCall): string;
}
