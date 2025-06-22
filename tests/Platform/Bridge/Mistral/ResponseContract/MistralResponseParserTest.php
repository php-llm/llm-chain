<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Mistral\ResponseContract;

use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\ResponseContract\MistralResponseParser;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MistralResponseParser::class)]
final class MistralResponseParserTest extends TestCase
{
    private MistralResponseParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MistralResponseParser();
    }

    public function testSupportsMistralModel(): void
    {
        $mistralModel = new Mistral(Mistral::MISTRAL_SMALL);
        $context = [ResponseContract::CONTEXT_MODEL => $mistralModel];

        $result = $this->parser->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertTrue($result);
    }

    public function testDoesNotSupportNonMistralModel(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        $context = [ResponseContract::CONTEXT_MODEL => $gptModel];

        $result = $this->parser->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertFalse($result);
    }

    public function testSupportsDenormalization(): void
    {
        $mistralModel = new Mistral(Mistral::MISTRAL_SMALL);
        $context = [ResponseContract::CONTEXT_MODEL => $mistralModel];

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
                        'content' => 'Bonjour, comment puis-je vous aider?',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
            ],
            'usage' => [
                'prompt_tokens' => 12,
                'completion_tokens' => 8,
                'total_tokens' => 20,
            ],
            'model' => 'mistral-small-latest',
            'id' => 'cmpl-mistral-123',
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new Mistral(Mistral::MISTRAL_SMALL)];
        $result = $this->parser->denormalize($responseData, LlmResponse::class, null, $context);

        self::assertInstanceOf(TextResponse::class, $result);
        self::assertSame('Bonjour, comment puis-je vous aider?', $result->getContent());
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
                                'id' => 'call_mistral_123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_weather',
                                    'arguments' => '{"location": "Paris", "unit": "celsius"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => 'tool_calls',
                    'index' => 0,
                ],
            ],
        ];

        $context = [ResponseContract::CONTEXT_MODEL => new Mistral(Mistral::MISTRAL_SMALL)];
        $result = $this->parser->denormalize($responseData, LlmResponse::class, null, $context);

        self::assertInstanceOf(ToolCallResponse::class, $result);
        $toolCalls = $result->getContent();
        self::assertCount(1, $toolCalls);

        $toolCall = $toolCalls[0];
        self::assertSame('call_mistral_123', $toolCall->id);
        self::assertSame('get_weather', $toolCall->name);
        self::assertSame(['location' => 'Paris', 'unit' => 'celsius'], $toolCall->arguments);
    }

    public function testDenormalizeThrowsExceptionForInvalidStructure(): void
    {
        $invalidData = ['invalid' => 'structure'];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid Mistral response structure: missing choices');

        $context = [ResponseContract::CONTEXT_MODEL => new Mistral(Mistral::MISTRAL_SMALL)];
        $this->parser->denormalize($invalidData, LlmResponse::class, null, $context);
    }

    public function testGetSupportedTypes(): void
    {
        $supportedTypes = $this->parser->getSupportedTypes(null);

        self::assertSame([LlmResponse::class => false], $supportedTypes);
    }
}
