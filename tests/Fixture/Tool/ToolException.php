<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_exception', description: 'This tool is broken', method: 'bar')]
final class ToolException
{
    public function bar(): string
    {
        throw new \Exception('Tool error.');
    }
}
