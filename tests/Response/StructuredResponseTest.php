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
    public function getContentWithArray(): void
    {
        $response = new StructuredResponse($expected = ['foo' => 'bar', 'baz' => ['qux']]);
        self::assertSame($expected, $response->getContent());
    }

    #[Test]
    public function getContentWithObject(): void
    {
        $response = new StructuredResponse($expected = (object) ['foo' => 'bar', 'baz' => ['qux']]);
        self::assertSame($expected, $response->getContent());
    }
}
