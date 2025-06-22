<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Denormalizer;

use PhpLlm\LlmChain\Platform\Contract\Denormalizer\StreamResponseDenormalizer;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\StreamResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(StreamResponseDenormalizer::class)]
final class StreamResponseDenormalizerTest extends TestCase
{
    private StreamResponseDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new StreamResponseDenormalizer();
        $mockDenormalizer = $this->createMock(DenormalizerInterface::class);
        $this->denormalizer->setDenormalizer($mockDenormalizer);
    }

    public function testSupportsDenormalizationForStreamingResponse(): void
    {
        $context = [ResponseContract::CONTEXT_OPTIONS => ['stream' => true]];

        $result = $this->denormalizer->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertTrue($result);
    }

    public function testDoesNotSupportDenormalizationForNonStreamingResponse(): void
    {
        $context = [ResponseContract::CONTEXT_OPTIONS => []];

        $result = $this->denormalizer->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertFalse($result);
    }

    public function testDenormalizeStreamResponse(): void
    {
        $mockHttpClient = new MockHttpClient();
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test', [
            'body' => 'data: {"choices":[{"delta":{"content":"Hello"}}]}'."\n\n".
                'data: [DONE]'."\n\n",
            'headers' => ['content-type' => 'text/event-stream'],
        ]);

        $context = [
            ResponseContract::CONTEXT_HTTP_RESPONSE => $mockResponse,
            ResponseContract::CONTEXT_OPTIONS => ['stream' => true],
        ];

        $result = $this->denormalizer->denormalize([], LlmResponse::class, null, $context);

        self::assertInstanceOf(StreamResponse::class, $result);

        // Verify it returns a generator
        $content = $result->getContent();
        self::assertInstanceOf(\Generator::class, $content);
    }

    public function testDenormalizeStreamResponseWithToolCalls(): void
    {
        $mockHttpClient = new MockHttpClient();
        $mockResponse = $mockHttpClient->request('GET', 'https://api.test', [
            'body' => 'data: {"choices":[{"delta":{"tool_calls":[{"id":"call_123"}]}}]}'."\n\n".
                'data: [DONE]'."\n\n",
            'headers' => ['content-type' => 'text/event-stream'],
        ]);

        $context = [
            ResponseContract::CONTEXT_HTTP_RESPONSE => $mockResponse,
            ResponseContract::CONTEXT_OPTIONS => ['stream' => true],
        ];

        $result = $this->denormalizer->denormalize([], LlmResponse::class, null, $context);

        self::assertInstanceOf(StreamResponse::class, $result);

        // Verify it returns a generator
        $content = $result->getContent();
        self::assertInstanceOf(\Generator::class, $content);
    }

    public function testGetSupportedTypes(): void
    {
        $supportedTypes = $this->denormalizer->getSupportedTypes(null);

        self::assertSame([LlmResponse::class => false], $supportedTypes);
    }
}
