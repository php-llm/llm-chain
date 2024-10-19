<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Response;

use PhpLlm\LlmChain\Model\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextResponse::class)]
#[Small]
final class TextResponseTest extends TestCase
{
    #[Test]
    public function getContent(): void
    {
        $response = new TextResponse($expected = 'foo');
        self::assertSame($expected, $response->getContent());
    }
}
