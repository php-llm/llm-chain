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

        self::assertSame(['role' => Role::User, 'content' => 'foo'], $obj->jsonSerialize());
        self::assertSame(Role::User, $obj->getRole());
    }

    #[Test]
    #[DataProvider('provideSerializationTests')]
    public function serializationResultsAsExpected(UserMessage $message, array $expectedArray): void
    {
        self::assertSame($message->jsonSerialize(), $expectedArray);
    }

    public static function provideSerializationTests(): \Generator
    {
        yield 'With only text' => [
            new UserMessage(new Text('foo')),
            ['role' => Role::User, 'content' => 'foo'],
        ];

        yield 'With single image' => [
            new UserMessage(new Text('foo'), new Image('bar')),
            [
                'role' => Role::User,
                'content' => [
                    ['type' => 'text', 'text' => 'foo'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'bar']],
                ],
            ],
        ];

        yield 'With single multiple images' => [
            new UserMessage(new Text('foo'), new Image('bar'), new Image('baz')),
            [
                'role' => Role::User,
                'content' => [
                    ['type' => 'text', 'text' => 'foo'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'bar']],
                    ['type' => 'image_url', 'image_url' => ['url' => 'baz']],
                ],
            ],
        ];
    }
}
