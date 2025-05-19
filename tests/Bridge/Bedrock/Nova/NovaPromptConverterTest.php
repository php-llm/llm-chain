<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\Bedrock\Nova;

use PhpLlm\LlmChain\Bridge\Bedrock\Nova\NovaPromptConverter;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(NovaPromptConverter::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(ToolCallMessage::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(MessageBag::class)]
final class NovaPromptConverterTest extends TestCase
{
    #[Test]
    #[DataProvider('provideMessageBag')]
    public function testConvert(MessageBag $bag, array $expected): void
    {
        $converter = new NovaPromptConverter();

        self::assertSame($expected, $converter->convertToPrompt($bag));
    }

    /**
     * @return iterable<array{0: MessageBag, 1: array}>
     */
    public static function provideMessageBag(): iterable
    {
        yield 'simple text' => [
            new MessageBag(Message::ofUser('Write a story about a magic backpack.')),
            [
                ['role' => 'user', 'content' => [['text' => 'Write a story about a magic backpack.']]],
            ],
        ];

        yield 'with assistant message' => [
            new MessageBag(
                Message::ofUser('Hello'),
                Message::ofAssistant('Great to meet you. What would you like to know?'),
                Message::ofUser('I have two dogs in my house. How many paws are in my house?'),
            ),
            [
                ['role' => 'user', 'content' => [['text' => 'Hello']]],
                ['role' => 'assistant', 'content' => [['text' => 'Great to meet you. What would you like to know?']]],
                ['role' => 'user', 'content' => [['text' => 'I have two dogs in my house. How many paws are in my house?']]],
            ],
        ];

        yield 'with system messages' => [
            new MessageBag(
                Message::forSystem('You are a cat. Your name is Neko.'),
                Message::ofUser('Hello there'),
            ),
            [
                ['role' => 'system', 'content' => [['text' => 'You are a cat. Your name is Neko.']]],
                ['role' => 'user', 'content' => [['text' => 'Hello there']]],
            ],
        ];

        yield 'with tool use' => [
            new MessageBag(
                Message::ofUser('Hello there, what is the time?'),
                Message::ofToolCall(new ToolCall('123456', 'clock', []), '2023-10-01T10:00:00+00:00'),
                Message::ofAssistant('It is 10:00 AM.'),
            ),
            [
                ['role' => 'user', 'content' => [['text' => 'Hello there, what is the time?']]],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'toolResult' => [
                                'toolUseId' => '123456',
                                'content' => [
                                    'text' => '2023-10-01T10:00:00+00:00',
                                ],
                            ],
                        ],
                    ],
                ],
                ['role' => 'assistant', 'content' => [['text' => 'It is 10:00 AM.']]],
            ],
        ];
    }
}
