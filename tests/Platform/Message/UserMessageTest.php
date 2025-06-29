<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\Role;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(UserMessage::class)]
#[UsesClass(Text::class)]
#[UsesClass(Audio::class)]
#[UsesClass(ImageUrl::class)]
#[UsesClass(Role::class)]
#[Small]
final class UserMessageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new UserMessage(new Text('foo'));

        self::assertSame(Role::User, $obj->getRole());
        self::assertCount(1, $obj->content);
        self::assertInstanceOf(Text::class, $obj->content[0]);
        self::assertSame('foo', $obj->content[0]->text);
    }

    #[Test]
    public function constructionIsPossibleWithMultipleContent(): void
    {
        $message = new UserMessage(new Text('foo'), new ImageUrl('https://foo.com/bar.jpg'));

        self::assertCount(2, $message->content);
    }

    #[Test]
    public function hasAudioContentWithoutAudio(): void
    {
        $message = new UserMessage(new Text('foo'), new Text('bar'));

        self::assertFalse($message->hasAudioContent());
    }

    #[Test]
    public function hasAudioContentWithAudio(): void
    {
        $message = new UserMessage(new Text('foo'), Audio::fromFile(\dirname(__DIR__, 2).'/Fixture/audio.mp3'));

        self::assertTrue($message->hasAudioContent());
    }

    #[Test]
    public function hasImageContentWithoutImage(): void
    {
        $message = new UserMessage(new Text('foo'), new Text('bar'));

        self::assertFalse($message->hasImageContent());
    }

    #[Test]
    public function hasImageContentWithImage(): void
    {
        $message = new UserMessage(new Text('foo'), new ImageUrl('https://foo.com/bar.jpg'));

        self::assertTrue($message->hasImageContent());
    }

    #[Test]
    public function messageHasUid(): void
    {
        $message = new UserMessage(new Text('foo'));

        self::assertInstanceOf(UuidV7::class, $message->uid);
        self::assertInstanceOf(UuidV7::class, $message->getUid());
        self::assertSame($message->uid, $message->getUid());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $message1 = new UserMessage(new Text('foo'));
        $message2 = new UserMessage(new Text('bar'));

        self::assertNotEquals($message1->getUid()->toRfc4122(), $message2->getUid()->toRfc4122());
    }
}
