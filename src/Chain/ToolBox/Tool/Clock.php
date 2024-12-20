<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Tool;

use Symfony\Component\Clock\ClockInterface;

final readonly class Clock
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(): string
    {
        return sprintf(
            'Current date is %s (YYYY-MM-DD) and the time is %s (HH:MM:SS).',
            $this->clock->now()->format('Y-m-d'),
            $this->clock->now()->format('H:i:s'),
        );
    }
}
