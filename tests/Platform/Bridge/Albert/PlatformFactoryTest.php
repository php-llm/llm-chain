<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\Albert\PlatformFactory;
use PhpLlm\LlmChain\Platform\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(PlatformFactory::class)]
#[Small]
final class PlatformFactoryTest extends TestCase
{
    #[Test]
    public function createsPlatformWithCorrectBaseUrl(): void
    {
        $platform = PlatformFactory::create('test-key', 'https://albert.example.com');

        self::assertInstanceOf(Platform::class, $platform);
    }

    #[Test]
    #[DataProvider('urlProvider')]
    public function handlesUrlsCorrectly(string $url): void
    {
        $platform = PlatformFactory::create('test-key', $url);

        self::assertInstanceOf(Platform::class, $platform);
    }

    public static function urlProvider(): array
    {
        return [
            'with trailing slash' => ['https://albert.example.com/'],
            'without trailing slash' => ['https://albert.example.com'],
            'with v1 path' => ['https://albert.example.com/v1'],
            'with v1 path and trailing slash' => ['https://albert.example.com/v1/'],
        ];
    }

    #[Test]
    public function throwsExceptionForNonHttpsUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Albert URL must start with "https://".');

        PlatformFactory::create('test-key', 'http://albert.example.com');
    }
}
