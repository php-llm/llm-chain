<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
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
}
