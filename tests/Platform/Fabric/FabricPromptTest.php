<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Fabric;

use PhpLlm\LlmChain\Platform\Fabric\FabricPrompt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FabricPrompt::class)]
#[Small]
final class FabricPromptTest extends TestCase
{
    #[Test]
    public function constructionAndGetters(): void
    {
        $prompt = new FabricPrompt(
            'test_pattern',
            '# Test Content',
            ['author' => 'Test Author']
        );

        self::assertSame('test_pattern', $prompt->getPattern());
        self::assertSame('# Test Content', $prompt->getContent());
        self::assertSame(['author' => 'Test Author'], $prompt->getMetadata());
    }

    #[Test]
    public function constructionWithoutMetadata(): void
    {
        $prompt = new FabricPrompt('test_pattern', '# Test Content');

        self::assertSame('test_pattern', $prompt->getPattern());
        self::assertSame('# Test Content', $prompt->getContent());
        self::assertSame([], $prompt->getMetadata());
    }
}
