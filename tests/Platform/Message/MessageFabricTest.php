<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[Small]
final class MessageFabricTest extends TestCase
{
    #[Test]
    public function fabricMethodThrowsExceptionWhenPackageNotInstalled(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package');

        Message::fabric('test_pattern');
    }
}