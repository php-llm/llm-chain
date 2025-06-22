<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\OpenAI\ResponseContract;

use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract\OpenAIResponseParser;
use PhpLlm\LlmChain\Platform\Response\ChoiceResponse;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenAIResponseParser::class)]
final class OpenAIResponseParserTest extends TestCase
{
    private OpenAIResponseParser $parser;

    protected function setUp(): void
    {
        $this->parser = new OpenAIResponseParser();
    }

    public function testSupportsGPTModel(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        $context = [ResponseContract::CONTEXT_MODEL => $gptModel];

        $result = $this->parser->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertTrue($result);
    }

    public function testDoesNotSupportNonGPTModel(): void
    {
        $mistralModel = new Mistral(Mistral::MISTRAL_SMALL);
        $context = [ResponseContract::CONTEXT_MODEL => $mistralModel];

        $result = $this->parser->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertFalse($result);
    }

    public function testSupportsDenormalization(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        $context = [ResponseContract::CONTEXT_MODEL => $gptModel];

        $result = $this->parser->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertTrue($result);
    }

    public function testDenormalizeSimpleTextResponse(): void
    {
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello, how can I help you?',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 7,
                'total_tokens' => 17,
            ],
            'model' => 'gpt-4o-mini',
            'id' => 'chatcmpl-123',
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        $result = $this->parser->denormalize($responseData, LlmResponse::class, null, $context);

        self::assertInstanceOf(TextResponse::class, $result);
        self::assertSame('Hello, how can I help you?', $result->getContent());
    }

    public function testDenormalizeResponseWithToolCalls(): void
    {
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [
                            [
                                'id' => 'call_123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_weather',
                                    'arguments' => '{"location": "London", "unit": "celsius"}',
                                ],
                            ],
                            [
                                'id' => 'call_456',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_time',
                                    'arguments' => '{"timezone": "UTC"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => 'tool_calls',
                    'index' => 0,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        $result = $this->parser->denormalize($responseData, LlmResponse::class, null, $context);

        self::assertInstanceOf(ToolCallResponse::class, $result);
        $toolCalls = $result->getContent();
        self::assertCount(2, $toolCalls);

        // First tool call
        $firstToolCall = $toolCalls[0];
        self::assertSame('call_123', $firstToolCall->id);
        self::assertSame('get_weather', $firstToolCall->name);
        self::assertSame(['location' => 'London', 'unit' => 'celsius'], $firstToolCall->arguments);

        // Second tool call
        $secondToolCall = $toolCalls[1];
        self::assertSame('call_456', $secondToolCall->id);
        self::assertSame('get_time', $secondToolCall->name);
        self::assertSame(['timezone' => 'UTC'], $secondToolCall->arguments);
    }

    public function testDenormalizeMultipleChoicesResponse(): void
    {
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'First choice',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Second choice',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 1,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        $result = $this->parser->denormalize($responseData, LlmResponse::class, null, $context);

        self::assertInstanceOf(ChoiceResponse::class, $result);
        $choices = $result->getContent();
        self::assertCount(2, $choices);

        self::assertSame('First choice', $choices[0]->getContent());
        self::assertSame('Second choice', $choices[1]->getContent());
    }

    public function testDenormalizeThrowsExceptionForInvalidStructure(): void
    {
        $invalidData = ['invalid' => 'structure'];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid OpenAI response structure: missing choices');

        $context = [ResponseContract::CONTEXT_MODEL => new GPT(GPT::GPT_4O_MINI)];
        $this->parser->denormalize($invalidData, LlmResponse::class, null, $context);
    }

    public function testGetSupportedTypes(): void
    {
        $supportedTypes = $this->parser->getSupportedTypes(null);

        self::assertSame([LlmResponse::class => false], $supportedTypes);
    }
}
