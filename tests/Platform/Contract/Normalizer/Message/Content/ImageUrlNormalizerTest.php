<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\ImageUrlNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageUrlNormalizer::class)]
final class ImageUrlNormalizerTest extends TestCase
{
    private ImageUrlNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ImageUrlNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new ImageUrl('https://example.com/image.jpg')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([ImageUrl::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalize(): void
    {
        $imageUrl = new ImageUrl('https://example.com/image.jpg');

        $expected = [
            'type' => 'image_url',
            'image_url' => ['url' => 'https://example.com/image.jpg'],
        ];

        self::assertSame($expected, $this->normalizer->normalize($imageUrl));
    }
}
