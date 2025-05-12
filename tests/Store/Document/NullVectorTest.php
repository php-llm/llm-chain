<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Store\Document;

use PhpLlm\LlmChain\Platform\Vector\NullVector;
use PhpLlm\LlmChain\Platform\Vector\VectorInterface;
use PhpLlm\LlmChain\Store\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullVector::class)]
final class NullVectorTest extends TestCase
{
    #[Test]
    public function implementsInterface(): void
    {
        self::assertInstanceOf(VectorInterface::class, new NullVector());
    }

    #[Test]
    public function getDataThrowsOnAccess(): void
    {
        self::expectException(RuntimeException::class);

        (new NullVector())->getData();
    }

    #[Test]
    public function getDimensionsThrowsOnAccess(): void
    {
        self::expectException(RuntimeException::class);

        (new NullVector())->getDimensions();
    }
}
