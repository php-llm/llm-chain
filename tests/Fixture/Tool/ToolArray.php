<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_array', 'A tool with array parameters')]
final class ToolArray
{
    /**
     * @param string[]  $urls
     * @param list<int> $ids
     */
    public function __invoke(array $urls, array $ids): string
    {
        return 'Hello world!';
    }
}
