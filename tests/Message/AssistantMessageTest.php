<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message;

use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AssistantMessage::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class AssistantMessageTest extends TestCase
{
    #[Test]
    public function theRoleOfTheMessageIsAsExpected(): void
    {
        self::assertSame(Role::Assistant, (new AssistantMessage())->getRole());
    }

    #[Test]
    public function constructionWithoutToolCallIsPossible(): void
    {
        $message = new AssistantMessage('foo');

        self::assertSame('foo', $message->content);
        self::assertNull($message->toolCalls);
    }

    #[Test]
    public function constructionWithoutContentIsPossible(): void
    {
        $toolCall = new ToolCall('foo', 'foo');
        $message = new AssistantMessage(toolCalls: [$toolCall]);

        self::assertNull($message->content);
        self::assertSame([$toolCall], $message->toolCalls);
        self::assertTrue($message->hasToolCalls());
    }

    #[Test]
    public function constructionWithMultipleToolCalls(): void
    {
        $toolCalls = [
            new ToolCall('call_123456', 'my_tool', ['foo' => 'bar']),
            new ToolCall('call_456789', 'my_faster_tool'),
        ];
        $message = new AssistantMessage(toolCalls: $toolCalls);

        self::assertCount(2, $message->toolCalls);
        self::assertTrue($message->hasToolCalls());
    }

    #[Test]
    #[DataProvider('provideJsonSerializerTests')]
    public function jsonConversionIsWorkingAsExpected(AssistantMessage $message, array $expectedResult): void
    {
        self::assertEqualsCanonicalizing($expectedResult, $message->jsonSerialize());
    }

    public static function provideJsonSerializerTests(): \Generator
    {
        yield 'Message with content' => [
            new AssistantMessage('Foo Bar Baz'),
            ['role' => Role::Assistant, 'content' => 'Foo Bar Baz'],
        ];

        $toolCall1 = new ToolCall('call_123456', 'my_tool', ['foo' => 'bar']);
        $toolCall2 = new ToolCall('call_456789', 'my_faster_tool');

        yield 'Message with tool calls' => [
            new AssistantMessage(toolCalls: [$toolCall1, $toolCall2]),
            ['role' => Role::Assistant, 'tool_calls' => [$toolCall1, $toolCall2]],
        ];
    }
}
