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

namespace PhpLlm\LlmChain\Tests\Response;

use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Choice::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ChoiceTest extends TestCase
{
    #[Test]
    public function choiceEmpty(): void
    {
        $choice = new Choice();
        self::assertFalse($choice->hasContent());
        self::assertNull($choice->getContent());
        self::assertFalse($choice->hasToolCall());
        self::assertCount(0, $choice->getToolCalls());
    }

    #[Test]
    public function choiceWithContent(): void
    {
        $choice = new Choice('content');
        self::assertTrue($choice->hasContent());
        self::assertSame('content', $choice->getContent());
        self::assertFalse($choice->hasToolCall());
        self::assertCount(0, $choice->getToolCalls());
    }

    #[Test]
    public function choiceWithToolCall(): void
    {
        $choice = new Choice(null, [new ToolCall('name', 'arguments')]);
        self::assertFalse($choice->hasContent());
        self::assertNull($choice->getContent());
        self::assertTrue($choice->hasToolCall());
        self::assertCount(1, $choice->getToolCalls());
    }

    #[Test]
    public function choiceWithContentAndToolCall(): void
    {
        $choice = new Choice('content', [new ToolCall('name', 'arguments')]);
        self::assertTrue($choice->hasContent());
        self::assertSame('content', $choice->getContent());
        self::assertTrue($choice->hasToolCall());
        self::assertCount(1, $choice->getToolCalls());
    }
}
