<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Vector;

use PhpLlm\LlmChain\Document\Vector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vector::class)]
final class VectorTest extends TestCase
{
    #[Test]
    public function withDimensionNull(): void
    {
        $actual = new Vector([1.0, 2.0, 3.0], null);
        $expected = [1.0, 2.0, 3.0];

        self::assertSame($expected, $actual->getData());
        self::assertSame(3, $actual->getDimensions());
    }
}
