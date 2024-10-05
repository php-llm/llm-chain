<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Response;

use PhpLlm\LlmChain\Response\StructuredResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructuredResponse::class)]
#[Small]
final class StructuredResponseTest extends TestCase
{
    #[Test]
    public function getContent(): void
    {
        $response = new StructuredResponse(['data1', 'data2']);
        self::assertSame(['data1', 'data2'], $response->getContent());
    }
}
