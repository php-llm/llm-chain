<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Image::class)]
#[Small]
final class ImageTest extends TestCase
{
    #[Test]
    public function constructWithValidUrl(): void
    {
        $image = new Image('https://foo.com/test.png');

        self::assertSame('https://foo.com/test.png', $image->url);
    }

    #[Test]
    public function constructWithValidDataUrl(): void
    {
        $image = new Image('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABKklEQVR42mNk+A8AAwMhIv9n+X');

        self::assertStringStartsWith('data:image/png;base64', $image->url);
    }

    #[Test]
    public function withValidFile(): void
    {
        $image = new Image(dirname(__DIR__, 3).'/Fixture/image.jpg');

        self::assertStringStartsWith('data:image/jpg;base64,', $image->url);
    }

    #[Test]
    public function fromBinaryWithInvalidFile(): void
    {
        $this->expectExceptionMessage('The file "foo.jpg" does not exist or is not readable.');

        new Image('foo.jpg');
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $image = new Image('https://foo.com/test.png');

        self::assertSame(['type' => 'image_url', 'image_url' => ['url' => 'https://foo.com/test.png']], $image->jsonSerialize());
    }
}
