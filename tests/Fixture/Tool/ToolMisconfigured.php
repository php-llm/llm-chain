<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_misconfigured', description: 'This tool is misconfigured, see method', method: 'foo')]
final class ToolMisconfigured
{
    public function bar(): string
    {
        return 'Wrong Config Attribute';
    }
}
