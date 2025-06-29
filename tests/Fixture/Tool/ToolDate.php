<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_date', 'A tool with date parameter')]
final class ToolDate
{
    /**
     * @param \DateTimeImmutable $date The date
     */
    public function __invoke(\DateTimeImmutable $date): string
    {
        return \sprintf('Weekday: %s', $date->format('l'));
    }
}
