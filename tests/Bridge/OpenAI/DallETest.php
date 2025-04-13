<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\OpenAI;

use PhpLlm\LlmChain\Bridge\OpenAI\DallE;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DallE::class)]
#[Small]
final class DallETest extends TestCase
{
    #[Test]
    public function itCreatesDallEWithDefaultSettings(): void
    {
        $dallE = new DallE();

        self::assertSame(DallE::DALL_E_2, $dallE->getName());
        self::assertSame([], $dallE->getOptions());
    }

    #[Test]
    public function itCreatesDallEWithCustomSettings(): void
    {
        $dallE = new DallE(DallE::DALL_E_3, ['response_format' => 'base64', 'n' => 2]);

        self::assertSame(DallE::DALL_E_3, $dallE->getName());
        self::assertSame(['response_format' => 'base64', 'n' => 2], $dallE->getOptions());
    }
}
