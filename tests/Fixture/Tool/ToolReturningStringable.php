<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_returning_stringable', 'A tool returning an object which implements \Stringable')]
final class ToolReturningStringable
{
    public function __invoke(): \Stringable
    {
        return new class implements \Stringable {
            public function __toString(): string
            {
                return 'Hi!';
            }
        };
    }
}
