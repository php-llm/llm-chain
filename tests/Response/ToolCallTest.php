<?php

declare(strict_types=1);

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
