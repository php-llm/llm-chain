<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\Role;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(SystemMessage::class)]
#[CoversClass(AssistantMessage::class)]
#[CoversClass(UserMessage::class)]
#[CoversClass(ToolCallMessage::class)]
#[CoversClass(MessageBag::class)]
#[UsesClass(Message::class)]
#[UsesClass(Role::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(Text::class)]
#[Small]
final class MessageIdTest extends TestCase
{
    #[Test]
    public function systemMessageHasDeterministicId(): void
    {
        $message1 = Message::forSystem('System prompt');
        $message2 = Message::forSystem('System prompt');
        $message3 = Message::forSystem('Different prompt');

        self::assertNotEmpty($message1->getId());
        self::assertTrue($message1->getId()->equals($message2->getId()));
        self::assertFalse($message1->getId()->equals($message3->getId()));
    }

    #[Test]
    public function assistantMessageHasDeterministicId(): void
    {
        $message1 = Message::ofAssistant('Hello there');
        $message2 = Message::ofAssistant('Hello there');
        $message3 = Message::ofAssistant('Different content');

        self::assertNotEmpty($message1->getId());
        self::assertTrue($message1->getId()->equals($message2->getId()));
        self::assertFalse($message1->getId()->equals($message3->getId()));
    }

    #[Test]
    public function assistantMessageWithToolCallsHasDeterministicId(): void
    {
        $toolCall1 = new ToolCall('call_123', 'test_tool', ['param' => 'value']);
        $toolCall2 = new ToolCall('call_456', 'other_tool');

        $message1 = Message::ofAssistant('Content', [$toolCall1]);
        $message2 = Message::ofAssistant('Content', [$toolCall1]);
        $message3 = Message::ofAssistant('Content', [$toolCall2]);
        $message4 = Message::ofAssistant('Different content', [$toolCall1]);

        self::assertNotEmpty($message1->getId());
        self::assertTrue($message1->getId()->equals($message2->getId()));
        self::assertFalse($message1->getId()->equals($message3->getId()));
        self::assertFalse($message1->getId()->equals($message4->getId()));
    }

    #[Test]
    public function userMessageHasDeterministicId(): void
    {
        $message1 = Message::ofUser('Hello');
        $message2 = Message::ofUser('Hello');
        $message3 = Message::ofUser('Different message');

        self::assertNotEmpty($message1->getId());
        self::assertTrue($message1->getId()->equals($message2->getId()));
        self::assertFalse($message1->getId()->equals($message3->getId()));
    }

    #[Test]
    public function toolCallMessageHasDeterministicId(): void
    {
        $toolCall1 = new ToolCall('call_123', 'test_tool', ['param' => 'value']);
        $toolCall2 = new ToolCall('call_456', 'other_tool');

        $message1 = Message::ofToolCall($toolCall1, 'Result 1');
        $message2 = Message::ofToolCall($toolCall1, 'Result 1');
        $message3 = Message::ofToolCall($toolCall2, 'Result 1');
        $message4 = Message::ofToolCall($toolCall1, 'Result 2');

        self::assertNotEmpty($message1->getId());
        self::assertTrue($message1->getId()->equals($message2->getId()));
        self::assertFalse($message1->getId()->equals($message3->getId()));
        self::assertFalse($message1->getId()->equals($message4->getId()));
    }

    #[Test]
    public function differentMessageTypesHaveDifferentIds(): void
    {
        $content = 'Same content';

        $systemMessage = Message::forSystem($content);
        $assistantMessage = Message::ofAssistant($content);
        $userMessage = Message::ofUser($content);

        self::assertFalse($systemMessage->getId()->equals($assistantMessage->getId()));
        self::assertFalse($systemMessage->getId()->equals($userMessage->getId()));
        self::assertFalse($assistantMessage->getId()->equals($userMessage->getId()));
    }

    #[Test]
    public function messageBagCanFindMessageById(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');

        $bag = new MessageBag($message1, $message2, $message3);

        self::assertSame($message1, $bag->findById($message1->getId()));
        self::assertSame($message2, $bag->findById($message2->getId()));
        self::assertSame($message3, $bag->findById($message3->getId()));
        self::assertNull($bag->findById(Uuid::v4())); // Random UUID
    }

    #[Test]
    public function messageBagCanCheckIfIdExists(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');

        $bag = new MessageBag($message1, $message2);

        self::assertTrue($bag->hasMessageWithId($message1->getId()));
        self::assertTrue($bag->hasMessageWithId($message2->getId()));
        self::assertFalse($bag->hasMessageWithId(Uuid::v4())); // Random UUID
    }

    #[Test]
    public function messageBagCanGetAllIds(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');

        $bag = new MessageBag($message1, $message2, $message3);
        $ids = $bag->getIds();

        self::assertCount(3, $ids);
        self::assertTrue($message1->getId()->equals($ids[0]));
        self::assertTrue($message2->getId()->equals($ids[1]));
        self::assertTrue($message3->getId()->equals($ids[2]));
    }

    #[Test]
    public function messageBagCanGetMessagesAfterId(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');
        $message4 = Message::ofUser('Another user message');

        $bag = new MessageBag($message1, $message2, $message3, $message4);

        $messagesAfterMessage2 = $bag->messagesAfterId($message2->getId());
        self::assertCount(2, $messagesAfterMessage2);
        self::assertSame($message3, $messagesAfterMessage2[0]);
        self::assertSame($message4, $messagesAfterMessage2[1]);

        // If ID not found, should return all messages
        $allMessages = $bag->messagesAfterId(Uuid::v4()); // Random UUID
        self::assertCount(4, $allMessages);
    }

    #[Test]
    public function messageBagCanGetMessagesNewerThan(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');
        $message4 = Message::ofUser('Another user message');

        $bag = new MessageBag($message1, $message2, $message3, $message4);

        $newerBag = $bag->messagesNewerThan($message1->getId());
        $newerMessages = $newerBag->getMessages();

        self::assertCount(3, $newerMessages);
        self::assertSame($message2, $newerMessages[0]);
        self::assertSame($message3, $newerMessages[1]);
        self::assertSame($message4, $newerMessages[2]);
    }

    #[Test]
    public function idIsValidUuid(): void
    {
        $message = Message::forSystem('Test message');
        $id = $message->getId();

        // Should be a valid UUID
        self::assertInstanceOf(Uuid::class, $id);
    }
}
