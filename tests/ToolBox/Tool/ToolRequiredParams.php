<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Tests\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\AsTool;

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
