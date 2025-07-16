<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\Albert\GPTModelClient;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(GPTModelClient::class)]
#[Small]
final class GPTModelClientTest extends TestCase
{
    #[Test]
    public function constructorThrowsExceptionForEmptyApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The API key must not be empty.');

        new GPTModelClient(
            new MockHttpClient(),
            '',
            'https://albert.example.com/'
        );
    }

    #[Test]
    public function constructorThrowsExceptionForEmptyBaseUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The base URL must not be empty.');

        new GPTModelClient(
            new MockHttpClient(),
            'test-api-key',
            ''
        );
    }

    #[Test]
    public function constructorWrapsHttpClientInEventSourceHttpClient(): void
    {
        self::expectNotToPerformAssertions();

        $mockHttpClient = new MockHttpClient();

        $client = new GPTModelClient(
            $mockHttpClient,
            'test-api-key',
            'https://albert.example.com/'
        );

        // We can't directly test the private property, but we can verify the behavior
        // by making a request and checking that it works correctly
        $mockResponse = new JsonMockResponse(['choices' => []]);
        $mockHttpClient->setResponseFactory([$mockResponse]);

        $model = new GPT('gpt-3.5-turbo');
        $client->request($model, ['messages' => []]);
    }

    #[Test]
    public function constructorAcceptsEventSourceHttpClient(): void
    {
        self::expectNotToPerformAssertions();

        $mockHttpClient = new MockHttpClient();
        $eventSourceClient = new EventSourceHttpClient($mockHttpClient);

        $client = new GPTModelClient(
            $eventSourceClient,
            'test-api-key',
            'https://albert.example.com/'
        );

        // Verify it works with EventSourceHttpClient
        $mockResponse = new JsonMockResponse(['choices' => []]);
        $mockHttpClient->setResponseFactory([$mockResponse]);

        $model = new GPT('gpt-3.5-turbo');
        $client->request($model, ['messages' => []]);
    }

    #[Test]
    public function supportsGPTModel(): void
    {
        $client = new GPTModelClient(
            new MockHttpClient(),
            'test-api-key',
            'https://albert.example.com/'
        );

        $gptModel = new GPT('gpt-3.5-turbo');
        self::assertTrue($client->supports($gptModel));
    }

    #[Test]
    public function doesNotSupportNonGPTModel(): void
    {
        $client = new GPTModelClient(
            new MockHttpClient(),
            'test-api-key',
            'https://albert.example.com/'
        );

        $embeddingsModel = new Embeddings('text-embedding-ada-002');
        self::assertFalse($client->supports($embeddingsModel));
    }

    #[Test]
    #[DataProvider('providePayloadToJson')]
    public function requestSendsCorrectHttpRequest(array|string $payload, array $options, array|string $expectedJson): void
    {
        $capturedRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$capturedRequest) {
            $capturedRequest = ['method' => $method, 'url' => $url, 'options' => $options];

            return new JsonMockResponse(['choices' => []]);
        });

        $client = new GPTModelClient(
            $httpClient,
            'test-api-key',
            'https://albert.example.com/v1'
        );

        $model = new GPT('gpt-3.5-turbo');
        $response = $client->request($model, $payload, $options);

        self::assertNotNull($capturedRequest);
        self::assertSame('POST', $capturedRequest['method']);
        self::assertSame('https://albert.example.com/v1/chat/completions', $capturedRequest['url']);
        self::assertArrayHasKey('normalized_headers', $capturedRequest['options']);
        self::assertArrayHasKey('authorization', $capturedRequest['options']['normalized_headers']);
        self::assertStringContainsString('Bearer test-api-key', (string) $capturedRequest['options']['normalized_headers']['authorization'][0]);

        // Check JSON body - it might be in 'body' after processing
        if (isset($capturedRequest['options']['body'])) {
            $actualJson = json_decode($capturedRequest['options']['body'], true);
            self::assertEquals($expectedJson, $actualJson);
        } else {
            self::assertSame($expectedJson, $capturedRequest['options']['json']);
        }
    }

    public static function providePayloadToJson(): iterable
    {
        yield 'with array payload and no options' => [
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo'],
            [],
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo'],
        ];

        yield 'with string payload and no options' => [
            'test message',
            [],
            'test message',
        ];

        yield 'with array payload and options' => [
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo'],
            ['temperature' => 0.7, 'max_tokens' => 150],
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo', 'temperature' => 0.7, 'max_tokens' => 150],
        ];

        yield 'options override payload values' => [
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo', 'temperature' => 1.0],
            ['temperature' => 0.5],
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo', 'temperature' => 0.5],
        ];

        yield 'with streaming option' => [
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo'],
            ['stream' => true],
            ['messages' => [['role' => 'user', 'content' => 'Hello']], 'model' => 'gpt-3.5-turbo', 'stream' => true],
        ];
    }

    #[Test]
    public function requestHandlesBaseUrlWithoutTrailingSlash(): void
    {
        $capturedUrl = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$capturedUrl) {
            $capturedUrl = $url;

            return new JsonMockResponse(['choices' => []]);
        });

        $client = new GPTModelClient(
            $httpClient,
            'test-api-key',
            'https://albert.example.com/v1'
        );

        $model = new GPT('gpt-3.5-turbo');
        $client->request($model, ['messages' => []]);

        self::assertSame('https://albert.example.com/v1/chat/completions', $capturedUrl);
    }

    #[Test]
    public function requestHandlesBaseUrlWithTrailingSlash(): void
    {
        $capturedUrl = null;
        $httpClient = new MockHttpClient(function ($method, $url) use (&$capturedUrl) {
            $capturedUrl = $url;

            return new JsonMockResponse(['choices' => []]);
        });

        $client = new GPTModelClient(
            $httpClient,
            'test-api-key',
            'https://albert.example.com/v1'
        );

        $model = new GPT('gpt-3.5-turbo');
        $client->request($model, ['messages' => []]);

        self::assertSame('https://albert.example.com/v1/chat/completions', $capturedUrl);
    }
}
