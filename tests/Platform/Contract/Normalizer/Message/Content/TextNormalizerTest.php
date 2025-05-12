<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\TextNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextNormalizer::class)]
#[UsesClass(Text::class)]
final class TextNormalizerTest extends TestCase
{
    private TextNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new TextNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new Text('Hello, world!')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([Text::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalize(): void
    {
        $text = new Text('Hello, world!');

        $expected = [
            'type' => 'text',
            'text' => 'Hello, world!',
        ];

        self::assertSame($expected, $this->normalizer->normalize($text));
    }
}
