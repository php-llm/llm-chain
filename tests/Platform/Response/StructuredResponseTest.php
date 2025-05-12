<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\ObjectResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectResponse::class)]
#[Small]
final class StructuredResponseTest extends TestCase
{
    #[Test]
    public function getContentWithArray(): void
    {
        $response = new ObjectResponse($expected = ['foo' => 'bar', 'baz' => ['qux']]);
        self::assertSame($expected, $response->getContent());
    }

    #[Test]
    public function getContentWithObject(): void
    {
        $response = new ObjectResponse($expected = (object) ['foo' => 'bar', 'baz' => ['qux']]);
        self::assertSame($expected, $response->getContent());
    }
}
