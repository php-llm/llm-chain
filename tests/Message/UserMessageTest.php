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
#[Small]
final class UserMessageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new UserMessage('bar');

        self::assertSame('bar', $obj->content);
        self::assertSame([], $obj->images);
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
        yield 'With only string content' => [
            new UserMessage('foo'),
            ['role' => Role::User, 'content' => 'foo'],
        ];

        yield 'With only TextContent' => [
            new UserMessage(new Text('foo')),
            ['role' => Role::User, 'content' => 'foo'],
        ];

        yield 'With single image as string' => [
            new UserMessage('foo', 'bar'),
            [
                'role' => Role::User,
                'content' => [
                    ['type' => 'text', 'text' => 'foo'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'bar']],
                ],
            ],
        ];

        yield 'With single image as ImageUrlContent' => [
            new UserMessage('foo', new Image('bar')),
            [
                'role' => Role::User,
                'content' => [
                    ['type' => 'text', 'text' => 'foo'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'bar']],
                ],
            ],
        ];

        yield 'With single mixed images' => [
            new UserMessage('foo', 'bar', new Image('baz')),
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
