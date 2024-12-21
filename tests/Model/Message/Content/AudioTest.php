<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Audio::class)]
#[Small]
final class AudioTest extends TestCase
{
    #[Test]
    public function constructWithValidPath(): void
    {
        $audio = new Audio(dirname(__DIR__, 3).'/Fixture/audio.mp3');

        self::assertSame(dirname(__DIR__, 3).'/Fixture/audio.mp3', $audio->path);
    }

    #[Test]
    #[DataProvider('provideValidPaths')]
    public function jsonSerializeWithValid(string $path, array $expected): void
    {
        $audio = new Audio($path);

        $expected = [
            'type' => 'input_audio',
            'input_audio' => $expected,
        ];

        $actual = $audio->jsonSerialize();

        // shortening the base64 data
        $actual['input_audio']['data'] = substr($actual['input_audio']['data'], 0, 30);

        self::assertSame($expected, $actual);
    }

    public static function provideValidPaths(): \Generator
    {
        yield 'mp3' => [dirname(__DIR__, 3).'/Fixture/audio.mp3', [
            'data' => 'SUQzBAAAAAAAfVREUkMAAAAMAAADMj', // shortened
            'format' => 'mp3',
        ]];
    }

    #[Test]
    public function constructWithInvalidPath(): void
    {
        $this->expectExceptionMessage('The file "foo.mp3" does not exist or is not readable.');

        new Audio('foo.mp3');
    }
}
