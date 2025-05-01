<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Image::class)]
final class ImageTest extends TestCase
{
    #[Test]
    public function constructWithValidDataUrl(): void
    {
        $image = Image::fromDataUrl('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABKklEQVR42mNk+A8AAwMhIv9n+X');

        self::assertStringStartsWith('data:image/png;base64', $image->asDataUrl());
    }

    #[Test]
    public function withValidFile(): void
    {
        $image = Image::fromFile(dirname(__DIR__, 3).'/Fixture/image.jpg');

        self::assertStringStartsWith('data:image/jpeg;base64,', $image->asDataUrl());
    }

    #[Test]
    public function fromBinaryWithInvalidFile(): void
    {
        self::expectExceptionMessage('The file "foo.jpg" does not exist or is not readable.');

        Image::fromFile('foo.jpg');
    }
}
