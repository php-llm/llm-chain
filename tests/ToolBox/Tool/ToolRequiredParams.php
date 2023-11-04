<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\Tests\ToolBox\Tool;

use SymfonyLlm\LlmChain\ToolBox\AsTool;

#[AsTool('tool_required_params', 'A tool with required parameters', method: 'bar')]
final class ToolRequiredParams
{
    /**
     * @param string $text   The text given to the tool
     * @param int    $number A number given to the tool
     */
    public function bar(string $text, int $number): string
    {
        return sprintf('%s says "%d".', $text, $number);
    }
}
