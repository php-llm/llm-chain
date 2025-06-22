<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\OpenAI\ResponseContract;

use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract\OpenAIStreamParser;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenAIStreamParser::class)]
final class OpenAIStreamParserTest extends TestCase
{
    private OpenAIStreamParser $parser;

    protected function setUp(): void
    {
        $this->parser = new OpenAIStreamParser();
    }

    public function testSupportsGPTModel(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        $context = [ResponseContract::CONTEXT_MODEL => $gptModel];

        $result = $this->parser->supportsDenormalization([], 'stream_chunk', null, $context);

        self::assertTrue($result);
    }

    public function testDoesNotSupportNonGPTModel(): void
    {
        $mistralModel = new Mistral(Mistral::MISTRAL_SMALL);
        $context = [ResponseContract::CONTEXT_MODEL => $mistralModel];

        $result = $this->parser->supportsDenormalization([], 'stream_chunk', null, $context);

        self::assertFalse($result);
    }

    public function testSupportsDenormalization(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        $context = [ResponseContract::CONTEXT_MODEL => $gptModel];

        $result = $this->parser->supportsDenormalization([], 'stream_chunk', null, $context);

        self::assertTrue($result);
    }

    public function testDenormalizeTextStreamChunk(): void
    {
        $streamData = [
            'choices' => [
                [
                    'delta' => [
                        'content' => 'Hello world',
                    ],
                    'finish_reason' => null,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $result */
        $result = $this->parser->denormalize($streamData, 'stream_chunk', null, $context);

        self::assertSame('Hello world', $result['textDelta']);
        self::assertEmpty($result['toolCallDeltas']);
        self::assertNull($result['finishReason']);
        self::assertFalse($result['isDone']);
    }

    public function testDenormalizeStreamChunkWithFinishReason(): void
    {
        $streamData = [
            'choices' => [
                [
                    'delta' => [],
                    'finish_reason' => 'stop',
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $result */
        $result = $this->parser->denormalize($streamData, 'stream_chunk', null, $context);

        self::assertNull($result['textDelta']);
        self::assertEmpty($result['toolCallDeltas']);
        self::assertSame('stop', $result['finishReason']);
        self::assertFalse($result['isDone']);
    }

    public function testDenormalizeToolCallStreamChunk(): void
    {
        $streamData = [
            'choices' => [
                [
                    'delta' => [
                        'tool_calls' => [
                            [
                                'id' => 'call_123',
                                'function' => [
                                    'name' => 'get_weather',
                                    'arguments' => '{"location"',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => null,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $result */
        $result = $this->parser->denormalize($streamData, 'stream_chunk', null, $context);

        self::assertNull($result['textDelta']);
        self::assertNull($result['finishReason']);
        self::assertFalse($result['isDone']);

        self::assertCount(1, $result['toolCallDeltas']);
        $toolCallDelta = $result['toolCallDeltas'][0];
        self::assertSame('call_123', $toolCallDelta['id']);
        self::assertSame('get_weather', $toolCallDelta['name']);
        self::assertSame('{"location"', $toolCallDelta['arguments']);
    }

    public function testDenormalizeToolCallStreamChunkArguments(): void
    {
        $streamData = [
            'choices' => [
                [
                    'delta' => [
                        'tool_calls' => [
                            [
                                'function' => [
                                    'arguments' => ': "London"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => null,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $result */
        $result = $this->parser->denormalize($streamData, 'stream_chunk', null, $context);

        self::assertNull($result['textDelta']);
        self::assertNull($result['finishReason']);
        self::assertFalse($result['isDone']);

        self::assertCount(1, $result['toolCallDeltas']);
        $toolCallDelta = $result['toolCallDeltas'][0];
        self::assertArrayNotHasKey('id', $toolCallDelta);
        self::assertArrayNotHasKey('name', $toolCallDelta);
        self::assertSame(': "London"}', $toolCallDelta['arguments']);
    }

    public function testDenormalizeMultipleToolCallsStreamChunk(): void
    {
        $streamData = [
            'choices' => [
                [
                    'delta' => [
                        'tool_calls' => [
                            [
                                'id' => 'call_1',
                                'function' => [
                                    'name' => 'function_1',
                                    'arguments' => '{"arg1"',
                                ],
                            ],
                            [
                                'function' => [
                                    'arguments' => ': "value1"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => null,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $result */
        $result = $this->parser->denormalize($streamData, 'stream_chunk', null, $context);

        self::assertCount(2, $result['toolCallDeltas']);

        // First tool call (new)
        $firstDelta = $result['toolCallDeltas'][0];
        self::assertSame('call_1', $firstDelta['id']);
        self::assertSame('function_1', $firstDelta['name']);
        self::assertSame('{"arg1"', $firstDelta['arguments']);

        // Second tool call (continuation)
        $secondDelta = $result['toolCallDeltas'][1];
        self::assertArrayNotHasKey('id', $secondDelta);
        self::assertArrayNotHasKey('name', $secondDelta);
        self::assertSame(': "value1"}', $secondDelta['arguments']);
    }

    public function testDenormalizeEmptyStreamChunk(): void
    {
        $streamData = [
            'choices' => [
                [
                    'delta' => [],
                    'finish_reason' => null,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $result */
        $result = $this->parser->denormalize($streamData, 'stream_chunk', null, $context);

        self::assertNull($result['textDelta']);
        self::assertEmpty($result['toolCallDeltas']);
        self::assertNull($result['finishReason']);
        self::assertFalse($result['isDone']);
    }

    public function testGetSupportedTypes(): void
    {
        $supportedTypes = $this->parser->getSupportedTypes(null);

        self::assertSame(['stream_chunk' => false], $supportedTypes);
    }
}
