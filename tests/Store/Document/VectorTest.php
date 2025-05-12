<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Store\Document;

use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Platform\Vector\VectorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vector::class)]
final class VectorTest extends TestCase
{
    #[Test]
    public function implementsInterface(): void
    {
        self::assertInstanceOf(
            VectorInterface::class,
            new Vector([1.0, 2.0, 3.0])
        );
    }

    #[Test]
    public function withDimensionNull(): void
    {
        $vector = new Vector($vectors = [1.0, 2.0, 3.0], null);

        self::assertSame($vectors, $vector->getData());
        self::assertSame(3, $vector->getDimensions());
    }
}
