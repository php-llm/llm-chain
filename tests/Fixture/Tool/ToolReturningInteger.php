<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_returning_integer', 'A tool returning an integer')]
final class ToolReturningInteger
{
    public function __invoke(): int
    {
        return 42;
    }
}
