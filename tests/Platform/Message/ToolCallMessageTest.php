<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Tests\Helper\UuidAssertionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\TimeBasedUidInterface;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ToolCallMessage::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ToolCallMessageTest extends TestCase
{
    use UuidAssertionTrait;

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

        self::assertInstanceOf(UuidV7::class, $message->id);
        self::assertInstanceOf(UuidV7::class, $message->getId());
        self::assertSame($message->id, $message->getId());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $message1 = new ToolCallMessage($toolCall, 'bar');
        $message2 = new ToolCallMessage($toolCall, 'baz');

        self::assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function sameMessagesHaveDifferentUids(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $message1 = new ToolCallMessage($toolCall, 'bar');
        $message2 = new ToolCallMessage($toolCall, 'bar');

        self::assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function messageIdImplementsRequiredInterfaces(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $message = new ToolCallMessage($toolCall, 'test');

        self::assertInstanceOf(AbstractUid::class, $message->getId());
        self::assertInstanceOf(TimeBasedUidInterface::class, $message->getId());
        self::assertInstanceOf(UuidV7::class, $message->getId());
    }
}
