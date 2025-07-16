<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\Albert\PlatformFactory;
use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Platform\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlatformFactory::class)]
#[Small]
final class PlatformFactoryTest extends TestCase
{
    #[Test]
    public function createsPlatformWithCorrectBaseUrl(): void
    {
        $platform = PlatformFactory::create('test-key', 'https://albert.example.com/v1');

        self::assertInstanceOf(Platform::class, $platform);
    }

    #[Test]
    #[DataProvider('provideValidUrls')]
    public function handlesUrlsCorrectly(string $url): void
    {
        $platform = PlatformFactory::create('test-key', $url);

        self::assertInstanceOf(Platform::class, $platform);
    }

    public static function provideValidUrls(): \Iterator
    {
        yield 'with v1 path' => ['https://albert.example.com/v1'];
        yield 'with v2 path' => ['https://albert.example.com/v2'];
        yield 'with v3 path' => ['https://albert.example.com/v3'];
        yield 'with v10 path' => ['https://albert.example.com/v10'];
        yield 'with v99 path' => ['https://albert.example.com/v99'];
    }

    #[Test]
    public function throwsExceptionForNonHttpsUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Albert URL must start with "https://".');

        PlatformFactory::create('test-key', 'http://albert.example.com');
    }

    #[Test]
    #[DataProvider('provideUrlsWithTrailingSlash')]
    public function throwsExceptionForUrlsWithTrailingSlash(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Albert URL must not end with a trailing slash.');

        PlatformFactory::create('test-key', $url);
    }

    public static function provideUrlsWithTrailingSlash(): \Iterator
    {
        yield 'with trailing slash only' => ['https://albert.example.com/'];
        yield 'with v1 and trailing slash' => ['https://albert.example.com/v1/'];
        yield 'with v2 and trailing slash' => ['https://albert.example.com/v2/'];
    }

    #[Test]
    #[DataProvider('provideUrlsWithoutVersion')]
    public function throwsExceptionForUrlsWithoutVersion(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Albert URL must include an API version (e.g., /v1, /v2).');

        PlatformFactory::create('test-key', $url);
    }

    public static function provideUrlsWithoutVersion(): \Iterator
    {
        yield 'without version' => ['https://albert.example.com'];
        yield 'with vx path' => ['https://albert.example.com/vx'];
        yield 'with version path' => ['https://albert.example.com/version'];
        yield 'with api path' => ['https://albert.example.com/api'];
        yield 'with v path only' => ['https://albert.example.com/v'];
        yield 'with v- path' => ['https://albert.example.com/v-1'];
    }
}
