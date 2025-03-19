<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_no_params', 'A tool without parameters')]
final class ToolNoParams
{
    public function __invoke(): string
    {
        return 'Hello world!';
    }
}
