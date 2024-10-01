<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message;

use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageBag::class)]
#[UsesClass(Message::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(Image::class)]
#[UsesClass(Text::class)]
#[Small]
final class MessageBagTest extends TestCase
{
    #[Test]
    public function getSystemMessage(): void
    {
        $messageBag = new MessageBag(
            new SystemMessage('My amazing system prompt.'),
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        $systemMessage = $messageBag->getSystemMessage();

        self::assertSame('My amazing system prompt.', $systemMessage->content);
    }

    #[Test]
    public function getSystemMessageWithoutSystemMessage(): void
    {
        $messageBag = new MessageBag(
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        self::assertNull($messageBag->getSystemMessage());
    }

    #[Test]
    public function with(): void
    {
        $messageBag = new MessageBag(
            new SystemMessage('My amazing system prompt.'),
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        $newMessage = new AssistantMessage('It is time to wake up.');
        $newMessageBag = $messageBag->with($newMessage);

        self::assertCount(3, $messageBag);
        self::assertCount(4, $newMessageBag);

        $newMessageFromBag = $newMessageBag[3];

        self::assertInstanceOf(AssistantMessage::class, $newMessageFromBag);
        self::assertSame('It is time to wake up.', $newMessageFromBag->content);
    }

    #[Test]
    public function merge(): void
    {
        $messageBag = new MessageBag(
            new SystemMessage('My amazing system prompt.'),
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        $messageBag = $messageBag->merge(new MessageBag(
            new AssistantMessage('It is time to wake up.')
        ));

        self::assertCount(4, $messageBag);

        $messageFromBag = $messageBag[3];

        self::assertInstanceOf(AssistantMessage::class, $messageFromBag);
        self::assertSame('It is time to wake up.', $messageFromBag->content);
    }

    #[Test]
    public function withoutSystemMessage(): void
    {
        $messageBag = new MessageBag(
            new SystemMessage('My amazing system prompt.'),
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        $newMessageBag = $messageBag->withoutSystemMessage();

        self::assertCount(3, $messageBag);
        self::assertCount(2, $newMessageBag);

        $messageFromNewBag = $newMessageBag[0];

        self::assertInstanceOf(AssistantMessage::class, $messageFromNewBag);
        self::assertSame('It is time to sleep.', $messageFromNewBag->content);
    }

    #[Test]
    public function prepend(): void
    {
        $messageBag = new MessageBag(
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        $newMessage = new SystemMessage('My amazing system prompt.');
        $newMessageBag = $messageBag->prepend($newMessage);

        self::assertCount(2, $messageBag);
        self::assertCount(3, $newMessageBag);

        $newMessageBagMessage = $newMessageBag[0];

        self::assertInstanceOf(SystemMessage::class, $newMessageBagMessage);
        self::assertSame('My amazing system prompt.', $newMessageBagMessage->content);
    }

    #[Test]
    public function containsImageWithoutImage(): void
    {
        $messageBag = new MessageBag(
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
        );

        self::assertFalse($messageBag->containsImage());
    }

    #[Test]
    public function containsImageWithImage(): void
    {
        $messageBag = new MessageBag(
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
            new UserMessage('My hint for how to analyze an image.', new Image('http://image-generator.local/my-fancy-image.png')),
        );

        self::assertTrue($messageBag->containsImage());
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $messageBag = new MessageBag(
            new SystemMessage('My amazing system prompt.'),
            new AssistantMessage('It is time to sleep.'),
            new UserMessage('Hello, world!'),
            new AssistantMessage('Hello User!'),
            new UserMessage('My hint for how to analyze an image.', new Image('http://image-generator.local/my-fancy-image.png')),
        );

        $json = json_encode($messageBag);

        self::assertJson($json);
        self::assertJsonStringEqualsJsonString(json_encode([
            ['role' => 'system', 'content' => 'My amazing system prompt.'],
            ['role' => 'assistant', 'content' => 'It is time to sleep.'],
            ['role' => 'user', 'content' => 'Hello, world!'],
            ['role' => 'assistant', 'content' => 'Hello User!'],
            ['role' => 'user', 'content' => [
                ['type' => 'text', 'text' => 'My hint for how to analyze an image.'],
                ['type' => 'image_url', 'image_url' => ['url' => 'http://image-generator.local/my-fancy-image.png']],
            ]],
        ]), $json);
    }
}
