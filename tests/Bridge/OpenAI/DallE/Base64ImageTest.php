<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\OpenAI\DallE;

use PhpLlm\LlmChain\Bridge\OpenAI\DallE\Base64Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Base64Image::class)]
#[Small]
final class Base64ImageTest extends TestCase
{
    #[Test]
    public function itCreatesBase64Image(): void
    {
        $emptyPixel = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $base64Image = new Base64Image($emptyPixel);

        self::assertSame($emptyPixel, $base64Image->encodedImage);
    }

    #[Test]
    public function itThrowsExceptionWhenBase64ImageIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The base64 encoded image generated must be given.');

        new Base64Image('');
    }
}
