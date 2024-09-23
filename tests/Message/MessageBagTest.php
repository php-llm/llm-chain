<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message;

use PhpLlm\LlmChain\Message\ImageUrl;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageBag::class)]
#[UsesClass(Message::class)]
#[Small]
final class MessageBagTest extends TestCase
{
    #[Test]
    public function getSystemMessage(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $systemMessage = $messageBag->getSystemMessage();

        self::assertSame('My amazing system prompt.', $systemMessage->content);
    }

    #[Test]
    public function getSystemMessageWithoutSystemMessage(): void
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        self::assertNull($messageBag->getSystemMessage());
    }

    #[Test]
    public function with(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $newMessage = Message::ofAssistant('It is time to wake up.');
        $newMessageBag = $messageBag->with($newMessage);

        self::assertCount(3, $messageBag);
        self::assertCount(4, $newMessageBag);
        self::assertSame('It is time to wake up.', $newMessageBag[3]->content);
    }

    #[Test]
    public function merge(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $messageBag = $messageBag->merge(new MessageBag(
            Message::ofAssistant('It is time to wake up.')
        ));

        self::assertCount(4, $messageBag);
        self::assertSame('It is time to wake up.', $messageBag[3]->content);
    }

    #[Test]
    public function withoutSystemMessage(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $newMessageBag = $messageBag->withoutSystemMessage();

        self::assertCount(3, $messageBag);
        self::assertCount(2, $newMessageBag);
        self::assertSame('It is time to sleep.', $newMessageBag[0]->content);
    }

    #[Test]
    public function prepend(): void
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $newMessage = Message::forSystem('My amazing system prompt.');
        $newMessageBag = $messageBag->prepend($newMessage);

        self::assertCount(2, $messageBag);
        self::assertCount(3, $newMessageBag);
        self::assertSame('My amazing system prompt.', $newMessageBag[0]->content);
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
            Message::ofUser('Analyze the following image:'),
            Message::ofUser(new ImageUrl('http://example.com/image.jpg')),
        );

        $json = json_encode($messageBag);

        self::assertJson($json);
        self::assertJsonStringEqualsJsonString(
            json_encode([
                ['role' => 'system', 'content' => 'My amazing system prompt.'],
                ['role' => 'assistant', 'content' => 'It is time to sleep.'],
                ['role' => 'user', 'content' => 'Hello, world!'],
                ['role' => 'user', 'content' => 'Analyze the following image:'],
                ['role' => 'user', 'content' => [
                    ['type' => 'image_url', 'image_url' => ['url' => 'http://example.com/image.jpg']],
                ]],
            ]),
            $json
        );
    }
}
