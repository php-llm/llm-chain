<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\OpenAI\DallE;

use PhpLlm\LlmChain\Bridge\OpenAI\DallE\Base64Image;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\ImageResponse;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\UrlImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageResponse::class)]
#[UsesClass(Base64Image::class)]
#[UsesClass(UrlImage::class)]
#[Small]
final class ImageResponseTest extends TestCase
{
    #[Test]
    public function itCreatesImagesResponse(): void
    {
        $base64Image = new Base64Image('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $generatedImagesResponse = new ImageResponse(null, $base64Image);

        self::assertNull($generatedImagesResponse->revisedPrompt);
        self::assertCount(1, $generatedImagesResponse->getContent());
        self::assertSame($base64Image, $generatedImagesResponse->getContent()[0]);
    }

    #[Test]
    public function itCreatesImagesResponseWithRevisedPrompt(): void
    {
        $base64Image = new Base64Image('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $generatedImagesResponse = new ImageResponse('revised prompt', $base64Image);

        self::assertSame('revised prompt', $generatedImagesResponse->revisedPrompt);
        self::assertCount(1, $generatedImagesResponse->getContent());
        self::assertSame($base64Image, $generatedImagesResponse->getContent()[0]);
    }

    #[Test]
    public function itIsCreatableWithMultipleImages(): void
    {
        $image1 = new UrlImage('https://example');
        $image2 = new UrlImage('https://example2');

        $generatedImagesResponse = new ImageResponse(null, $image1, $image2);

        self::assertCount(2, $generatedImagesResponse->getContent());
        self::assertSame($image1, $generatedImagesResponse->getContent()[0]);
        self::assertSame($image2, $generatedImagesResponse->getContent()[1]);
    }
}
