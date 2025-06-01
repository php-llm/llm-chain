<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message\Content;

use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageUrl::class)]
#[Small]
final class ImageUrlTest extends TestCase
{
    #[Test]
    public function constructWithValidUrl(): void
    {
        $image = new ImageUrl('https://foo.com/test.png');

        self::assertSame('https://foo.com/test.png', $image->url);
    }
}
