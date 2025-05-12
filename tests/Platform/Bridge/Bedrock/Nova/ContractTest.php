<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Bedrock\Nova;

use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract\ToolCallMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract\ToolNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract\UserMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Nova;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[Medium]
#[CoversClass(AssistantMessageNormalizer::class)]
#[CoversClass(MessageBagNormalizer::class)]
#[CoversClass(ToolCallMessageNormalizer::class)]
#[CoversClass(ToolNormalizer::class)]
#[CoversClass(UserMessageNormalizer::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(ToolCallMessage::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(MessageBag::class)]
final class ContractTest extends TestCase
{
    #[Test]
    #[DataProvider('provideMessageBag')]
    public function testConvert(MessageBag $bag, array $expected): void
    {
        $contract = Contract::create(
            new AssistantMessageNormalizer(),
            new MessageBagNormalizer(),
            new ToolCallMessageNormalizer(),
            new ToolNormalizer(),
            new UserMessageNormalizer(),
        );

        self::assertEquals($expected, $contract->createRequestPayload(new Nova(), $bag));
    }

    /**
     * @return iterable<array{0: MessageBag, 1: array}>
     */
    public static function provideMessageBag(): iterable
    {
        yield 'simple text' => [
            new MessageBag(Message::ofUser('Write a story about a magic backpack.')),
            [
                'messages' => [
                    ['role' => 'user', 'content' => [['text' => 'Write a story about a magic backpack.']]],
                ],
            ],
        ];

        yield 'with assistant message' => [
            new MessageBag(
                Message::ofUser('Hello'),
                Message::ofAssistant('Great to meet you. What would you like to know?'),
                Message::ofUser('I have two dogs in my house. How many paws are in my house?'),
            ),
            [
                'messages' => [
                    ['role' => 'user', 'content' => [['text' => 'Hello']]],
                    ['role' => 'assistant', 'content' => [['text' => 'Great to meet you. What would you like to know?']]],
                    ['role' => 'user', 'content' => [['text' => 'I have two dogs in my house. How many paws are in my house?']]],
                ],
            ],
        ];

        yield 'with system messages' => [
            new MessageBag(
                Message::forSystem('You are a cat. Your name is Neko.'),
                Message::ofUser('Hello there'),
            ),
            [
                'system' => [['text' => 'You are a cat. Your name is Neko.']],
                'messages' => [
                    ['role' => 'user', 'content' => [['text' => 'Hello there']]],
                ],
            ],
        ];

        yield 'with tool use' => [
            new MessageBag(
                Message::ofUser('Hello there, what is the time?'),
                Message::ofAssistant(toolCalls: [new ToolCall('123456', 'clock', [])]),
                Message::ofToolCall(new ToolCall('123456', 'clock', []), '2023-10-01T10:00:00+00:00'),
                Message::ofAssistant('It is 10:00 AM.'),
            ),
            [
                'messages' => [
                    ['role' => 'user', 'content' => [['text' => 'Hello there, what is the time?']]],
                    [
                        'role' => 'assistant',
                        'content' => [
                            [
                                'toolUse' => [
                                    'toolUseId' => '123456',
                                    'name' => 'clock',
                                    'input' => new \stdClass(),
                                ],
                            ],
                        ],
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'toolResult' => [
                                    'toolUseId' => '123456',
                                    'content' => [
                                        ['json' => '2023-10-01T10:00:00+00:00'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ['role' => 'assistant', 'content' => [['text' => 'It is 10:00 AM.']]],
                ],
            ],
        ];
    }
}
