<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Role;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Tests\Helper\UuidAssertionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\TimeBasedUidInterface;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(SystemMessage::class)]
#[Small]
final class SystemMessageTest extends TestCase
{
    use UuidAssertionTrait;

    #[Test]
    public function constructionIsPossible(): void
    {
        $message = new SystemMessage('foo');

        self::assertSame(Role::System, $message->getRole());
        self::assertSame('foo', $message->content);
    }

    #[Test]
    public function messageHasUid(): void
    {
        $message = new SystemMessage('foo');

        self::assertInstanceOf(UuidV7::class, $message->id);
        self::assertInstanceOf(UuidV7::class, $message->getId());
        self::assertSame($message->id, $message->getId());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $message1 = new SystemMessage('foo');
        $message2 = new SystemMessage('bar');

        self::assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function sameMessagesHaveDifferentUids(): void
    {
        $message1 = new SystemMessage('foo');
        $message2 = new SystemMessage('foo');

        self::assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function messageIdImplementsRequiredInterfaces(): void
    {
        $message = new SystemMessage('test');

        self::assertInstanceOf(AbstractUid::class, $message->getId());
        self::assertInstanceOf(TimeBasedUidInterface::class, $message->getId());
        self::assertInstanceOf(UuidV7::class, $message->getId());
    }
}
