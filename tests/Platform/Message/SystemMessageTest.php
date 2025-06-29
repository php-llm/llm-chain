<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Role;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(SystemMessage::class)]
#[Small]
final class SystemMessageTest extends TestCase
{
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

        self::assertInstanceOf(UuidV7::class, $message->uid);
        self::assertInstanceOf(UuidV7::class, $message->getUid());
        self::assertSame($message->uid, $message->getUid());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $message1 = new SystemMessage('foo');
        $message2 = new SystemMessage('bar');

        self::assertNotEquals($message1->getUid()->toRfc4122(), $message2->getUid()->toRfc4122());
    }
}
