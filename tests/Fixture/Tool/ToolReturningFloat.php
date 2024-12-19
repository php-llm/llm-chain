<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_returning_float', 'A tool returning a float')]
final class ToolReturningFloat
{
    public function __invoke(): float
    {
        return 42.42;
    }
}
