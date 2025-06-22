<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract\OpenAIResponseParser;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract\OpenAIStreamParser;
use PhpLlm\LlmChain\Platform\Contract\ResponseDenormalizer;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PhpLlm\LlmChain\Platform\ResponseContractFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ResponseContractFactory::class)]
final class ResponseContractFactoryTest extends TestCase
{
    private ResponseContractFactory $factory;
    private Model $model;

    protected function setUp(): void
    {
        $responseContract = new ResponseContract(
            new ResponseDenormalizer(
                new OpenAIResponseParser(),
                new OpenAIStreamParser(),
            )
        );
        $this->factory = new ResponseContractFactory($responseContract);
        $this->model = new GPT(GPT::GPT_4O_MINI);
    }

    public function testSupportsModel(): void
    {
        $gptModel = new GPT(GPT::GPT_4O_MINI);
        self::assertTrue($this->factory->supports($gptModel));
    }

    public function testConvert(): void
    {
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Test response',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
            ],
        ];

        $mockHttpClient = new MockHttpClient([new MockResponse(json_encode($responseData))]);
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test');

        // First call supports() to set the model
        $this->factory->supports($this->model);

        // Then call convert()
        $response = $this->factory->convert($mockResponse);

        self::assertInstanceOf(TextResponse::class, $response);
        self::assertSame('Test response', $response->getContent());
    }

    public function testConvertWithoutSupportsCallThrowsException(): void
    {
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Test response',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
            ],
        ];

        $mockHttpClient = new MockHttpClient([new MockResponse(json_encode($responseData))]);
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Model must be provided via supports() call first');

        $this->factory->convert($mockResponse);
    }

    public function testStateIsClearedAfterConvert(): void
    {
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'First response',
                    ],
                    'finish_reason' => 'stop',
                    'index' => 0,
                ],
            ],
        ];

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode($responseData)),
            new MockResponse(json_encode($responseData)),
        ]);
        $firstResponse = $mockHttpClient->request('GET', 'https://api.test');
        $secondResponse = $mockHttpClient->request('GET', 'https://api.test');

        // First call: supports() + convert() should work
        $this->factory->supports($this->model);
        $result = $this->factory->convert($firstResponse);
        self::assertInstanceOf(TextResponse::class, $result);

        // Second call: convert() without supports() should fail (state was cleared)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Model must be provided via supports() call first');

        $this->factory->convert($secondResponse);
    }
}
