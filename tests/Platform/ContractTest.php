<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Bridge\OpenAI\Whisper\AudioNormalizer;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\Contract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Contract::class)]
final class ContractTest extends TestCase
{
    #[Test]
    #[DataProvider('providePayloadTestCases')]
    public function createRequestPayload(Model $model, array|string|object $input, array|string $expected): void
    {
        $contract = Contract::create();

        $actual = $contract->createRequestPayload($model, $input);

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<string, array{
     *     input: array|string|object,
     *     expected: array<string, mixed>|string
     * }>
     */
    public static function providePayloadTestCases(): iterable
    {
        yield 'MessageBag with GPT' => [
            'model' => new GPT(),
            'input' => new MessageBag(
                Message::forSystem('System message'),
                Message::ofUser('User message'),
                Message::ofAssistant('Assistant message'),
            ),
            'expected' => [
                'messages' => [
                    ['role' => 'system', 'content' => 'System message'],
                    ['role' => 'user', 'content' => 'User message'],
                    ['role' => 'assistant', 'content' => 'Assistant message'],
                ],
                'model' => 'gpt-4o',
            ],
        ];

        $audio = Audio::fromFile(dirname(__DIR__, 2).'/tests/Fixture/audio.mp3');
        yield 'Audio within MessageBag with GPT' => [
            'model' => new GPT(),
            'input' => new MessageBag(Message::ofUser('What is this recording about?', $audio)),
            'expected' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'What is this recording about?'],
                            [
                                'type' => 'input_audio',
                                'input_audio' => [
                                    'data' => $audio->asBase64(),
                                    'format' => 'mp3',
                                ],
                            ],
                        ],
                    ],
                ],
                'model' => 'gpt-4o',
            ],
        ];

        $image = Image::fromFile(dirname(__DIR__, 2).'/tests/Fixture/image.jpg');
        yield 'Image within MessageBag with GPT' => [
            'model' => new GPT(),
            'input' => new MessageBag(
                Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
                Message::ofUser('Describe the image as a comedian would do it.', $image),
            ),
            'expected' => [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an image analyzer bot that helps identify the content of images.',
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'Describe the image as a comedian would do it.'],
                            ['type' => 'image_url', 'image_url' => ['url' => $image->asDataUrl()]],
                        ],
                    ],
                ],
                'model' => 'gpt-4o',
            ],
        ];

        yield 'ImageUrl within MessageBag with GPT' => [
            'model' => new GPT(),
            'input' => new MessageBag(
                Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
                Message::ofUser('Describe the image as a comedian would do it.', new ImageUrl('https://example.com/image.jpg')),
            ),
            'expected' => [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an image analyzer bot that helps identify the content of images.',
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'Describe the image as a comedian would do it.'],
                            ['type' => 'image_url', 'image_url' => ['url' => 'https://example.com/image.jpg']],
                        ],
                    ],
                ],
                'model' => 'gpt-4o',
            ],
        ];

        yield 'Text Input with Embeddings' => [
            'model' => new Embeddings(),
            'input' => 'This is a test input.',
            'expected' => 'This is a test input.',
        ];

        yield 'Longer Conversation with GPT' => [
            'model' => new GPT(),
            'input' => new MessageBag(
                Message::forSystem('My amazing system prompt.'),
                Message::ofAssistant('It is time to sleep.'),
                Message::ofUser('Hello, world!'),
                new AssistantMessage('Hello User!'),
                Message::ofUser('My hint for how to analyze an image.', new ImageUrl('http://image-generator.local/my-fancy-image.png')),
            ),
            'expected' => [
                'messages' => [
                    ['role' => 'system', 'content' => 'My amazing system prompt.'],
                    ['role' => 'assistant', 'content' => 'It is time to sleep.'],
                    ['role' => 'user', 'content' => 'Hello, world!'],
                    ['role' => 'assistant', 'content' => 'Hello User!'],
                    ['role' => 'user', 'content' => [
                        ['type' => 'text', 'text' => 'My hint for how to analyze an image.'],
                        ['type' => 'image_url', 'image_url' => ['url' => 'http://image-generator.local/my-fancy-image.png']],
                    ]],
                ],
                'model' => 'gpt-4o',
            ],
        ];
    }

    #[Test]
    public function extendedContractHandlesWhisper(): void
    {
        $contract = Contract::create(new AudioNormalizer());

        $audio = Audio::fromFile(dirname(__DIR__, 2).'/tests/Fixture/audio.mp3');

        $actual = $contract->createRequestPayload(new Whisper(), $audio);

        self::assertArrayHasKey('model', $actual);
        self::assertSame('whisper-1', $actual['model']);
        self::assertArrayHasKey('file', $actual);
        self::assertTrue(is_resource($actual['file']));
    }
}
