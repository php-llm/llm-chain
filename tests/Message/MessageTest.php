<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message;

use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(Message::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class MessageTest extends TestCase
{
    #[Test]
    public function createSystemMessage(): void
    {
        $message = Message::forSystem('My amazing system prompt.');

        self::assertSame('My amazing system prompt.', $message->content);
    }

    #[Test]
    public function createAssistantMessage(): void
    {
        $message = Message::ofAssistant('It is time to sleep.');

        self::assertSame('It is time to sleep.', $message->content);
    }

    #[Test]
    public function createAssistantMessageWithToolCalls(): void
    {
        $toolCalls = [
            new ToolCall('call_123456', 'my_tool', ['foo' => 'bar']),
            new ToolCall('call_456789', 'my_faster_tool'),
        ];
        $message = Message::ofAssistant(toolCalls: $toolCalls);

        self::assertCount(2, $message->toolCalls);
        self::assertTrue($message->hasToolCalls());
    }

    #[Test]
    public function createUserMessageWithoutContentThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least a single content part must be given.');

        Message::ofUser();
    }

    #[Test]
    public function createUserMessageWithoutTextContentInFirstPlaceIsNotPossible(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The first content piece has to be a string or Text part');

        Message::ofUser(new Image('foo'), 'bar');
    }

    #[Test]
    public function createUserMessage(): void
    {
        $message = Message::ofUser('Hi, my name is John.');

        self::assertSame('Hi, my name is John.', $message->text->text);
    }

    #[Test]
    public function createUserMessageWithTextContent(): void
    {
        $message = Message::ofUser(new Text('Hi, my name is John.'));

        self::assertSame('Hi, my name is John.', $message->text->text);
    }

    #[Test]
    public function createUserMessageWithImages(): void
    {
        $message = Message::ofUser('Hi, my name is John.', 'http://images.local/my-image.png', 'http://images.local/my-image2.png');

        self::assertSame('Hi, my name is John.', $message->text->text);
        self::assertCount(2, $message->images);
    }

    #[Test]
    public function createToolCallMessage(): void
    {
        $toolCall = new ToolCall('call_123456', 'my_tool', ['foo' => 'bar']);
        $message = Message::ofToolCall($toolCall, 'Foo bar.');

        self::assertSame('Foo bar.', $message->content);
        self::assertSame($toolCall, $message->toolCall);
    }
}
