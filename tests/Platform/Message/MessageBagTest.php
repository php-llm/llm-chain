<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
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
#[UsesClass(ImageUrl::class)]
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
            Message::ofUser('My hint for how to analyze an image.', new ImageUrl('http://image-generator.local/my-fancy-image.png')),
        );

        self::assertTrue($messageBag->containsImage());
    }
}
