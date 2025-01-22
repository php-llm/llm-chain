<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
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
#[UsesClass(ToolCall::class)]
#[UsesClass(ToolCallMessage::class)]
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
            Message::ofToolCall(new ToolCall('tool', 'tool_name', ['param' => 'value']), 'Yes, go sleeping.'),
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
            Message::ofToolCall(new ToolCall('tool', 'tool_name', ['param' => 'value']), 'Yes, go sleeping.'),
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

        $newMessageFromBag = $newMessageBag->getMessages()[3];

        self::assertInstanceOf(AssistantMessage::class, $newMessageFromBag);
        self::assertSame('It is time to wake up.', $newMessageFromBag->content);
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

        $messageFromBag = $messageBag->getMessages()[3];

        self::assertInstanceOf(AssistantMessage::class, $messageFromBag);
        self::assertSame('It is time to wake up.', $messageFromBag->content);
    }

    #[Test]
    public function withoutSystemMessage(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::forSystem('A system prompt in the middle.'),
            Message::ofUser('Hello, world!'),
            Message::forSystem('Another system prompt at the end'),
        );

        $newMessageBag = $messageBag->withoutSystemMessage();

        self::assertCount(5, $messageBag);
        self::assertCount(2, $newMessageBag);

        $assistantMessage = $newMessageBag->getMessages()[0];
        self::assertInstanceOf(AssistantMessage::class, $assistantMessage);
        self::assertSame('It is time to sleep.', $assistantMessage->content);

        $userMessage = $newMessageBag->getMessages()[1];
        self::assertInstanceOf(UserMessage::class, $userMessage);
        self::assertInstanceOf(Text::class, $userMessage->content[0]);
        self::assertSame('Hello, world!', $userMessage->content[0]->text);
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

        $newMessageBagMessage = $newMessageBag->getMessages()[0];

        self::assertInstanceOf(SystemMessage::class, $newMessageBagMessage);
        self::assertSame('My amazing system prompt.', $newMessageBagMessage->content);
    }

    #[Test]
    public function containsImageReturnsFalseWithoutImage(): void
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        self::assertFalse($messageBag->containsImage());
    }

    #[Test]
    public function containsImageReturnsTrueWithImage(): void
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
            Message::ofUser('My hint for how to analyze an image.', new Image('http://image-generator.local/my-fancy-image.png')),
        );

        self::assertTrue($messageBag->containsImage());
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
            new AssistantMessage('Hello User!'),
            Message::ofUser('My hint for how to analyze an image.', new Image('http://image-generator.local/my-fancy-image.png')),
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
