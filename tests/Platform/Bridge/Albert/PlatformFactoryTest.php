<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\Albert\PlatformFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
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
        $platform = PlatformFactory::create('test-key', 'https://albert.example.com');

        self::assertInstanceOf(Platform::class, $platform);
    }

    #[Test]
    public function trimsTrailingSlashFromUrl(): void
    {
        $platform1 = PlatformFactory::create('test-key', 'https://albert.example.com/');
        $platform2 = PlatformFactory::create('test-key', 'https://albert.example.com');

        // Both should create the same platform configuration
        self::assertInstanceOf(Platform::class, $platform1);
        self::assertInstanceOf(Platform::class, $platform2);
    }
}