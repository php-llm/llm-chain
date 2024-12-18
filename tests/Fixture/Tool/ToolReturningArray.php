<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_returning_array', 'A tool returning an array')]
final class ToolReturningArray
{
    public function __invoke(): array
    {
        return ['foo' => 'bar'];
    }

}
