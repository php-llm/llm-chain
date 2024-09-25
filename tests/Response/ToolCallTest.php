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

use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolCall::class)]
#[Small]
final class ToolCallTest extends TestCase
{
    #[Test]
    public function toolCall(): void
    {
        $toolCall = new ToolCall('id', 'name', ['foo' => 'bar']);
        self::assertSame('id', $toolCall->id);
        self::assertSame('name', $toolCall->name);
        self::assertSame(['foo' => 'bar'], $toolCall->arguments);
    }

    #[Test]
    public function toolCallJsonSerialize(): void
    {
        $toolCall = new ToolCall('id', 'name', ['foo' => 'bar']);
        self::assertSame(
            [
                'id' => 'id',
                'type' => 'function',
                'function' => [
                    'name' => 'name',
                    'arguments' => '{"foo":"bar"}',
                ],
            ],
            $toolCall->jsonSerialize()
        );
    }
}
