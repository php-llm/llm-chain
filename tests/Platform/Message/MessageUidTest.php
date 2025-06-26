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
final class MessageUidTest extends TestCase
{
    #[Test]
    public function systemMessageHasDeterministicUid(): void
    {
        $message1 = Message::forSystem('System prompt');
        $message2 = Message::forSystem('System prompt');
        $message3 = Message::forSystem('Different prompt');

        self::assertNotEmpty($message1->getUid());
        self::assertSame($message1->getUid(), $message2->getUid());
        self::assertNotSame($message1->getUid(), $message3->getUid());
    }

    #[Test]
    public function assistantMessageHasDeterministicUid(): void
    {
        $message1 = Message::ofAssistant('Hello there');
        $message2 = Message::ofAssistant('Hello there');
        $message3 = Message::ofAssistant('Different content');

        self::assertNotEmpty($message1->getUid());
        self::assertSame($message1->getUid(), $message2->getUid());
        self::assertNotSame($message1->getUid(), $message3->getUid());
    }

    #[Test]
    public function assistantMessageWithToolCallsHasDeterministicUid(): void
    {
        $toolCall1 = new ToolCall('call_123', 'test_tool', ['param' => 'value']);
        $toolCall2 = new ToolCall('call_456', 'other_tool');

        $message1 = Message::ofAssistant('Content', [$toolCall1]);
        $message2 = Message::ofAssistant('Content', [$toolCall1]);
        $message3 = Message::ofAssistant('Content', [$toolCall2]);
        $message4 = Message::ofAssistant('Different content', [$toolCall1]);

        self::assertNotEmpty($message1->getUid());
        self::assertSame($message1->getUid(), $message2->getUid());
        self::assertNotSame($message1->getUid(), $message3->getUid());
        self::assertNotSame($message1->getUid(), $message4->getUid());
    }

    #[Test]
    public function userMessageHasDeterministicUid(): void
    {
        $message1 = Message::ofUser('Hello');
        $message2 = Message::ofUser('Hello');
        $message3 = Message::ofUser('Different message');

        self::assertNotEmpty($message1->getUid());
        self::assertSame($message1->getUid(), $message2->getUid());
        self::assertNotSame($message1->getUid(), $message3->getUid());
    }

    #[Test]
    public function toolCallMessageHasDeterministicUid(): void
    {
        $toolCall1 = new ToolCall('call_123', 'test_tool', ['param' => 'value']);
        $toolCall2 = new ToolCall('call_456', 'other_tool');

        $message1 = Message::ofToolCall($toolCall1, 'Result 1');
        $message2 = Message::ofToolCall($toolCall1, 'Result 1');
        $message3 = Message::ofToolCall($toolCall2, 'Result 1');
        $message4 = Message::ofToolCall($toolCall1, 'Result 2');

        self::assertNotEmpty($message1->getUid());
        self::assertSame($message1->getUid(), $message2->getUid());
        self::assertNotSame($message1->getUid(), $message3->getUid());
        self::assertNotSame($message1->getUid(), $message4->getUid());
    }

    #[Test]
    public function differentMessageTypesHaveDifferentUids(): void
    {
        $content = 'Same content';
        
        $systemMessage = Message::forSystem($content);
        $assistantMessage = Message::ofAssistant($content);
        $userMessage = Message::ofUser($content);

        self::assertNotSame($systemMessage->getUid(), $assistantMessage->getUid());
        self::assertNotSame($systemMessage->getUid(), $userMessage->getUid());
        self::assertNotSame($assistantMessage->getUid(), $userMessage->getUid());
    }

    #[Test]
    public function messageBagCanFindMessageByUid(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');

        $bag = new MessageBag($message1, $message2, $message3);

        self::assertSame($message1, $bag->findByUid($message1->getUid()));
        self::assertSame($message2, $bag->findByUid($message2->getUid()));
        self::assertSame($message3, $bag->findByUid($message3->getUid()));
        self::assertNull($bag->findByUid('non-existent-uid'));
    }

    #[Test]
    public function messageBagCanCheckIfUidExists(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');

        $bag = new MessageBag($message1, $message2);

        self::assertTrue($bag->hasMessageWithUid($message1->getUid()));
        self::assertTrue($bag->hasMessageWithUid($message2->getUid()));
        self::assertFalse($bag->hasMessageWithUid('non-existent-uid'));
    }

    #[Test]
    public function messageBagCanGetAllUids(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');

        $bag = new MessageBag($message1, $message2, $message3);
        $uids = $bag->getUids();

        self::assertCount(3, $uids);
        self::assertSame($message1->getUid(), $uids[0]);
        self::assertSame($message2->getUid(), $uids[1]);
        self::assertSame($message3->getUid(), $uids[2]);
    }

    #[Test]
    public function messageBagCanGetMessagesAfterUid(): void
    {
        $message1 = Message::forSystem('System');
        $message2 = Message::ofUser('User message');
        $message3 = Message::ofAssistant('Assistant response');
        $message4 = Message::ofUser('Another user message');

        $bag = new MessageBag($message1, $message2, $message3, $message4);

        $messagesAfterMessage2 = $bag->messagesAfterUid($message2->getUid());
        self::assertCount(2, $messagesAfterMessage2);
        self::assertSame($message3, $messagesAfterMessage2[0]);
        self::assertSame($message4, $messagesAfterMessage2[1]);

        // If UID not found, should return all messages
        $allMessages = $bag->messagesAfterUid('non-existent-uid');
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

        $newerBag = $bag->messagesNewerThan($message1->getUid());
        $newerMessages = $newerBag->getMessages();

        self::assertCount(3, $newerMessages);
        self::assertSame($message2, $newerMessages[0]);
        self::assertSame($message3, $newerMessages[1]);
        self::assertSame($message4, $newerMessages[2]);
    }

    #[Test]
    public function uidIsValidSha256Hash(): void
    {
        $message = Message::forSystem('Test message');
        $uid = $message->getUid();

        // SHA256 hash should be 64 characters long and contain only hex characters
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $uid);
    }
}