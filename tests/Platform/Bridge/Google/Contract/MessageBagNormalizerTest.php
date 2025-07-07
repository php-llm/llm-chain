<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\UserMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Medium]
#[CoversClass(MessageBagNormalizer::class)]
#[CoversClass(UserMessageNormalizer::class)]
#[CoversClass(AssistantMessageNormalizer::class)]
#[UsesClass(Model::class)]
#[UsesClass(Gemini::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(AssistantMessage::class)]
final class MessageBagNormalizerTest extends TestCase
{
    #[Test]
    public function supportsNormalization(): void
    {
        $normalizer = new MessageBagNormalizer();

        self::assertTrue($normalizer->supportsNormalization(new MessageBag(), context: [
            Contract::CONTEXT_MODEL => new Gemini(),
        ]));
        self::assertFalse($normalizer->supportsNormalization('not a message bag'));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        $normalizer = new MessageBagNormalizer();

        $expected = [
            MessageBagInterface::class => true,
        ];

        self::assertSame($expected, $normalizer->getSupportedTypes(null));
    }

    #[Test]
    #[DataProvider('provideMessageBagData')]
    public function normalize(MessageBag $bag, array $expected): void
    {
        $normalizer = new MessageBagNormalizer();

        // Set up the inner normalizers
        $userMessageNormalizer = new UserMessageNormalizer();
        $assistantMessageNormalizer = new AssistantMessageNormalizer();

        // Mock a normalizer that delegates to the appropriate concrete normalizer
        $mockNormalizer = $this->createMock(NormalizerInterface::class);
        $mockNormalizer->method('normalize')
            ->willReturnCallback(function ($message) use ($userMessageNormalizer, $assistantMessageNormalizer): ?array {
                if ($message instanceof UserMessage) {
                    return $userMessageNormalizer->normalize($message);
                }
                if ($message instanceof AssistantMessage) {
                    return $assistantMessageNormalizer->normalize($message);
                }

                return null;
            });

        $normalizer->setNormalizer($mockNormalizer);

        $normalized = $normalizer->normalize($bag);

        self::assertEquals($expected, $normalized);
    }

    /**
     * @return iterable<array{0: MessageBag, 1: array}>
     */
    public static function provideMessageBagData(): iterable
    {
        yield 'simple text' => [
            new MessageBag(Message::ofUser('Write a story about a magic backpack.')),
            [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'Write a story about a magic backpack.']]],
                ],
            ],
        ];

        yield 'text with image' => [
            new MessageBag(
                Message::ofUser('Tell me about this instrument', Image::fromFile(\dirname(__DIR__, 4).'/Fixture/image.jpg'))
            ),
            [
                'contents' => [
                    ['role' => 'user', 'parts' => [
                        ['text' => 'Tell me about this instrument'],
                        ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => base64_encode(file_get_contents(\dirname(__DIR__, 4).'/Fixture/image.jpg'))]],
                    ]],
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
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'Hello']]],
                    ['role' => 'model', 'parts' => [['text' => 'Great to meet you. What would you like to know?']]],
                    ['role' => 'user', 'parts' => [['text' => 'I have two dogs in my house. How many paws are in my house?']]],
                ],
            ],
        ];

        yield 'with system messages' => [
            new MessageBag(
                Message::forSystem('You are a cat. Your name is Neko.'),
                Message::ofUser('Hello there'),
            ),
            [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'Hello there']]],
                ],
                'system_instruction' => [
                    'parts' => [['text' => 'You are a cat. Your name is Neko.']],
                ],
            ],
        ];
    }
}
