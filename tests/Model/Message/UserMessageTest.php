<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message;

use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserMessage::class)]
#[UsesClass(Text::class)]
#[UsesClass(Audio::class)]
#[UsesClass(Image::class)]
#[UsesClass(Role::class)]
#[Small]
final class UserMessageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new UserMessage(new Text('foo'));

        self::assertSame(\json_encode(['role' => Role::User, 'content' => 'foo']), \json_encode($obj));
        self::assertSame(Role::User, $obj->getRole());
    }

    #[Test]
    public function constructionIsPossibleWithMultipleContent(): void
    {
        $message = new UserMessage(new Text('foo'), new Image('https://foo.com/bar.jpg'));

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
        $message = new UserMessage(new Text('foo'), Audio::fromFile(dirname(__DIR__, 2).'/Fixture/audio.mp3'));

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
        $message = new UserMessage(new Text('foo'), new Image('https://foo.com/bar.jpg'));

        self::assertTrue($message->hasImageContent());
    }

    #[Test]
    #[DataProvider('provideSerializationTests')]
    public function serializationResultsAsExpected(UserMessage $message, array $expectedArray): void
    {
        self::assertSame(\json_encode($message), \json_encode($expectedArray));
    }

    public static function provideSerializationTests(): \Generator
    {
        yield 'With only text' => [
            new UserMessage(new Text('foo')),
            ['role' => Role::User, 'content' => 'foo'],
        ];

        yield 'With single image' => [
            new UserMessage(new Text('foo'), new Image('https://foo.com/bar.jpg')),
            [
                'role' => Role::User,
                'content' => [
                    ['type' => 'text', 'text' => 'foo'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'https://foo.com/bar.jpg']],
                ],
            ],
        ];

        yield 'With single multiple images' => [
            new UserMessage(new Text('foo'), new Image('https://foo.com/bar.jpg'), new Image('https://foo.com/baz.jpg')),
            [
                'role' => Role::User,
                'content' => [
                    ['type' => 'text', 'text' => 'foo'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'https://foo.com/bar.jpg']],
                    ['type' => 'image_url', 'image_url' => ['url' => 'https://foo.com/baz.jpg']],
                ],
            ],
        ];
    }
}
