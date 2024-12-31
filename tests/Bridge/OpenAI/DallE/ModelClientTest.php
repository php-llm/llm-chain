<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\OpenAI\DallE;

use PhpLlm\LlmChain\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\Base64Image;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\GeneratedImagesResponse;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\ModelClient;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\UrlImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

#[CoversClass(ModelClient::class)]
#[UsesClass(DallE::class)]
#[UsesClass(HttpResponse::class)]
#[UsesClass(HttpClientInterface::class)]
#[UsesClass(UrlImage::class)]
#[UsesClass(Base64Image::class)]
#[UsesClass(GeneratedImagesResponse::class)]
#[Small]
class ModelClientTest extends TestCase
{
    #[Test]
    public function itIsSupportingTheCorrectModel(): void
    {
        $modelClient = new ModelClient(self::createStub(HttpClientInterface::class), 'sk-api-key');

        self::assertTrue($modelClient->supports(new DallE(), 'foo'));
    }

    #[Test]
    public function itIsExecutingTheCorrectRequest(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with('POST', 'https://api.openai.com/v1/images/generations', [
                'auth_bearer' => 'sk-api-key',
                'json' => [
                    'model' => DallE::DALL_E_2,
                    'prompt' => 'foo',
                    'n' => 1,
                    'response_format' => 'url',
                ],
            ]);

        $modelClient = new ModelClient($httpClient, 'sk-api-key');
        $modelClient->request(new DallE(), 'foo', ['n' => 1, 'response_format' => 'url']);
    }

    #[Test]
    public function itIsConvertingTheResponse(): void
    {
        $httpResponse = self::createStub(HttpResponse::class);
        $httpResponse->method('toArray')->willReturn([
            'data' => [
                ['url' => 'https://example.com/image.jpg'],
            ],
        ]);

        $modelClient = new ModelClient(self::createStub(HttpClientInterface::class), 'sk-api-key');
        $response = $modelClient->convert($httpResponse, ['response_format' => 'url']);

        self::assertCount(1, $response->getContent());
        self::assertInstanceOf(UrlImage::class, $response->getContent()[0]);
        self::assertSame('https://example.com/image.jpg', $response->getContent()[0]->url);
    }

    #[Test]
    public function itIsConvertingTheResponseWithRevisedPrompt(): void
    {
        $emptyPixel = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $httpResponse = self::createStub(HttpResponse::class);
        $httpResponse->method('toArray')->willReturn([
            'data' => [
                ['b64_json' => $emptyPixel, 'revised_prompt' => 'revised prompt'],
            ],
        ]);

        $modelClient = new ModelClient(self::createStub(HttpClientInterface::class), 'sk-api-key');
        $response = $modelClient->convert($httpResponse, ['response_format' => 'b64_json']);

        self::assertInstanceOf(GeneratedImagesResponse::class, $response);
        self::assertCount(1, $response->getContent());
        self::assertInstanceOf(Base64Image::class, $response->getContent()[0]);
        self::assertSame($emptyPixel, $response->getContent()[0]->encodedImage);
        self::assertSame('revised prompt', $response->revisedPrompt);
    }
}
