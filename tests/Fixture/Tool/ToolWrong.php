<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

final class ToolWrong
{
    /**
     * @param string $text   The text given to the tool
     * @param int    $number A number given to the tool
     */
    public function bar(string $text, int $number): string
    {
        return \sprintf('%s says "%d".', $text, $number);
    }
}
