<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message\Content;

use PhpLlm\LlmChain\Message\Content\ImageUrlContent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageUrlContent::class)]
#[Small]
final class ImageUrlContentTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new ImageUrlContent('foo');

        self::assertSame('foo', $obj->imageUrl);
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $obj = new ImageUrlContent('foo');

        self::assertSame(
            ['type' => 'image_url', 'image_url' => ['url' => 'foo']],
            $obj->jsonSerialize(),
        );
    }
}
