<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\OpenAI\GPT;

use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter;
use PhpLlm\LlmChain\Exception\ContentFilterException;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Response\ChoiceResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ResponseConverter::class)]
#[Small]
class ResponseConverterTest extends TestCase
{
    public function testConvertTextResponse(): void
    {
        $converter = new ResponseConverter();
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello world',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
        ]);

        $response = $converter->convert($httpResponse);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertEquals('Hello world', $response->getContent());
    }

    public function testConvertToolCallResponse(): void
    {
        $converter = new ResponseConverter();
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('toArray')->willReturn([
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
                                    'name' => 'test_function',
                                    'arguments' => '{"arg1": "value1"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => 'tool_calls',
                ],
            ],
        ]);

        $response = $converter->convert($httpResponse);

        $this->assertInstanceOf(ToolCallResponse::class, $response);
        $toolCalls = $response->getContent();
        $this->assertCount(1, $toolCalls);
        $this->assertEquals('call_123', $toolCalls[0]->id);
        $this->assertEquals('test_function', $toolCalls[0]->name);
        $this->assertEquals(['arg1' => 'value1'], $toolCalls[0]->arguments);
    }

    public function testConvertMultipleChoices(): void
    {
        $converter = new ResponseConverter();
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Choice 1',
                    ],
                    'finish_reason' => 'stop',
                ],
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Choice 2',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
        ]);

        $response = $converter->convert($httpResponse);

        $this->assertInstanceOf(ChoiceResponse::class, $response);
        $choices = $response->getContent();
        $this->assertCount(2, $choices);
        $this->assertEquals('Choice 1', $choices[0]->getContent());
        $this->assertEquals('Choice 2', $choices[1]->getContent());
    }

    public function testContentFilterException(): void
    {
        $converter = new ResponseConverter();
        $httpResponse = $this->createMock(ResponseInterface::class);

        $httpResponse->expects($this->exactly(2))
            ->method('toArray')
            ->willReturnCallback(function ($throw = true) {
                if ($throw) {
                    throw new class extends \Exception implements ClientExceptionInterface {
                        public function getResponse(): ResponseInterface
                        {
                            throw new RuntimeException('Not implemented');
                        }
                    };
                }

                return [
                    'error' => [
                        'code' => 'content_filter',
                        'message' => 'Content was filtered',
                    ],
                ];
            });

        $this->expectException(ContentFilterException::class);
        $this->expectExceptionMessage('Content was filtered');

        $converter->convert($httpResponse);
    }

    public function testThrowsExceptionWhenNoChoices(): void
    {
        $converter = new ResponseConverter();
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('toArray')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Response does not contain choices');

        $converter->convert($httpResponse);
    }

    public function testThrowsExceptionForUnsupportedFinishReason(): void
    {
        $converter = new ResponseConverter();
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Test content',
                    ],
                    'finish_reason' => 'unsupported_reason',
                ],
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported finish reason "unsupported_reason"');

        $converter->convert($httpResponse);
    }
}
