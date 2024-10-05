<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message;

use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserMessage::class)]
#[UsesClass(Text::class)]
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
    public function constructionWithStringIsPossible(): void
    {
        $message = new UserMessage('Hi, my name is John.');

        self::assertCount(1, $message->content);
        self::assertInstanceOf(Text::class, $message->content[0]);
        self::assertSame('Hi, my name is John.', $message->content[0]->text);
    }

    #[Test]
    public function constructionIsPossibleWithMultipleContent(): void
    {
        $message = new UserMessage(new Text('foo'), new Image('https://foo.com/bar.jpg'));

        self::assertCount(2, $message->content);
    }

    #[Test]
    public function constructionIsPossibleWithMultipleMixedContent(): void
    {
        $message = new UserMessage(
            new Text('Hi, my name is John.'),
            new Image('http://images.local/my-image.png'),
            'The following image is a joke.',
            new Image('http://images.local/my-image2.png'),
        );

        self::assertCount(4, $message->content);
        self::assertSame(\json_encode([
            'role' => Role::User,
            'content' => [
                ['type' => 'text', 'text' => 'Hi, my name is John.'],
                ['type' => 'image_url', 'image_url' => ['url' => 'http://images.local/my-image.png']],
                ['type' => 'text', 'text' => 'The following image is a joke.'],
                ['type' => 'image_url', 'image_url' => ['url' => 'http://images.local/my-image2.png']],
            ],
        ]), \json_encode($message));
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
