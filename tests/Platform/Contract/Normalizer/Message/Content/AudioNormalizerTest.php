<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\AudioNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use PhpLlm\LlmChain\Platform\Message\Content\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AudioNormalizer::class)]
#[UsesClass(Audio::class)]
#[UsesClass(File::class)]
final class AudioNormalizerTest extends TestCase
{
    private AudioNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new AudioNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(Audio::fromFile(\dirname(__DIR__, 5).'/Fixture/audio.mp3')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([Audio::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    #[DataProvider('provideAudioData')]
    public function normalize(string $data, string $format, array $expected): void
    {
        $audio = new Audio(base64_decode($data), $format);

        self::assertSame($expected, $this->normalizer->normalize($audio));
    }

    public static function provideAudioData(): \Generator
    {
        yield 'mp3 data' => [
            'SUQzBAAAAAAAfVREUkMAAAAMAAADMg==',
            'audio/mpeg',
            [
                'type' => 'input_audio',
                'input_audio' => [
                    'data' => 'SUQzBAAAAAAAfVREUkMAAAAMAAADMg==',
                    'format' => 'mp3',
                ],
            ],
        ];

        yield 'wav data' => [
            'UklGRiQAAABXQVZFZm10IBA=',
            'audio/wav',
            [
                'type' => 'input_audio',
                'input_audio' => [
                    'data' => 'UklGRiQAAABXQVZFZm10IBA=',
                    'format' => 'wav',
                ],
            ],
        ];
    }
}
