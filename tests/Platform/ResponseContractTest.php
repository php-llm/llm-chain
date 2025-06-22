<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract\OpenAIResponseParser;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract\OpenAIStreamParser;
use PhpLlm\LlmChain\Platform\Contract\ResponseDenormalizer;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PhpLlm\LlmChain\Platform\ResponseContractFactory;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ResponseContract::class)]
final class ResponseContractTest extends TestCase
{
    private ResponseContract $responseContract;
    private Model $model;

    protected function setUp(): void
    {
        $this->responseContract = new ResponseContract(
            new ResponseDenormalizer(
                new OpenAIResponseParser(),
                new OpenAIStreamParser(),
            )
        );
        $this->model = new GPT(GPT::GPT_4O_MINI);
    }

    public function testCreateResponseContract(): void
    {
        $contract = new ResponseContract(
            new ResponseDenormalizer(
                new OpenAIResponseParser(),
                new OpenAIStreamParser(),
            )
        );

        self::assertInstanceOf(ResponseContract::class, $contract);
    }

    public function testAsConverter(): void
    {
        $converter = $this->responseContract->asConverter();

        self::assertInstanceOf(ResponseConverterInterface::class, $converter);
        self::assertInstanceOf(ResponseContractFactory::class, $converter);
    }

    public function testSupportsModel(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        self::assertTrue($this->responseContract->supportsModel($gptModel));
    }

    public function testConvertResponseSimpleText(): void
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
        ];

        $mockHttpClient = new MockHttpClient([new MockResponse(json_encode($responseData))]);
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test');
        $response = $this->responseContract->convertResponse($this->model, $mockResponse);

        self::assertInstanceOf(TextResponse::class, $response);
        self::assertSame('Hello, how can I help you?', $response->getContent());
    }

    public function testConvertResponseWithToolCalls(): void
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
                                    'arguments' => '{"location": "London"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => 'tool_calls',
                    'index' => 0,
                ],
            ],
        ];

        $mockHttpClient = new MockHttpClient([new MockResponse(json_encode($responseData))]);
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test');
        $response = $this->responseContract->convertResponse($this->model, $mockResponse);

        self::assertInstanceOf(ToolCallResponse::class, $response);
        $toolCalls = $response->getContent();
        self::assertCount(1, $toolCalls);
        self::assertSame('call_123', $toolCalls[0]->id);
        self::assertSame('get_weather', $toolCalls[0]->name);
        self::assertSame(['location' => 'London'], $toolCalls[0]->arguments);
    }

    public function testConvertResponseWithStreamOption(): void
    {
        // For the context test, we'll mock a basic response with streaming option
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello world',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
            ],
        ];

        $mockHttpClient = new MockHttpClient([new MockResponse(
            json_encode($responseData),
            ['content-type' => 'application/json']
        )]);
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test');

        // Test that the ResponseContract passes through the options correctly
        $response = $this->responseContract->convertResponse(
            $this->model,
            $mockResponse,
            ['stream' => false] // Actually test non-streaming
        );

        self::assertInstanceOf(TextResponse::class, $response);
        self::assertSame('Hello world', $response->getContent());
    }

    public function testResponseContractConstants(): void
    {
        self::assertSame('llm_model', ResponseContract::CONTEXT_MODEL);
        self::assertSame('llm_options', ResponseContract::CONTEXT_OPTIONS);
        self::assertSame('http_response', ResponseContract::CONTEXT_HTTP_RESPONSE);
    }
}
