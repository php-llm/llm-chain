<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\Tests\ToolBox\Tool;

use SymfonyLlm\LlmChain\ToolBox\AsTool;

#[AsTool('tool_no_params', 'A tool without parameters')]
final class ToolNoParams
{
    public function __invoke(): string
    {
        return 'Hello world!';
    }
}
