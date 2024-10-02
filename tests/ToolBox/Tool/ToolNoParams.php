<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;

#[AsTool('tool_no_params', 'A tool without parameters')]
final class ToolNoParams
{
    public function __invoke(): string
    {
        return 'Hello world!';
    }
}
