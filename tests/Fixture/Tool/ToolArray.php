<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_no_params', 'A tool without parameters')]
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
