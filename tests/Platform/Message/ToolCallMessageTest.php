<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ToolCallMessage::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ToolCallMessageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $obj = new ToolCallMessage($toolCall, 'bar');

        self::assertSame($toolCall, $obj->toolCall);
        self::assertSame('bar', $obj->content);
    }

    #[Test]
    public function messageHasUid(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $message = new ToolCallMessage($toolCall, 'bar');

        self::assertInstanceOf(UuidV7::class, $message->uid);
        self::assertInstanceOf(UuidV7::class, $message->getUid());
        self::assertSame($message->uid, $message->getUid());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $message1 = new ToolCallMessage($toolCall, 'bar');
        $message2 = new ToolCallMessage($toolCall, 'baz');

        self::assertNotEquals($message1->getUid()->toRfc4122(), $message2->getUid()->toRfc4122());
    }
}
