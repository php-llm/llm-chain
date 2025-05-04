<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\ImageNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageNormalizer::class)]
final class ImageNormalizerTest extends TestCase
{
    private ImageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ImageNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(Image::fromFile(dirname(__DIR__, 5).'/Fixture/image.jpg')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([Image::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalize(): void
    {
        $image = Image::fromDataUrl('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABKklEQVR42mNk+A8AAwMhIv9n+Q==');

        $expected = [
            'type' => 'image_url',
            'image_url' => ['url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABKklEQVR42mNk+A8AAwMhIv9n+Q=='],
        ];

        self::assertSame($expected, $this->normalizer->normalize($image));
    }
}
