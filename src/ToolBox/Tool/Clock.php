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

namespace PhpLlm\LlmChain\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\AsTool;
use Symfony\Component\Clock\ClockInterface;

#[AsTool('clock', description: 'Provides the current date and time.')]
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
