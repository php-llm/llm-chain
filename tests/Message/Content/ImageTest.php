<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message\Content;

use PhpLlm\LlmChain\Message\Content\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Image::class)]
#[Small]
final class ImageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new Image('foo');

        self::assertSame('foo', $obj->image);
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $obj = new Image('foo');

        self::assertSame(
            ['type' => 'image_url', 'image_url' => ['url' => 'foo']],
            $obj->jsonSerialize(),
        );
    }
}
