<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Document;

use PhpLlm\LlmChain\Document\NullVector;
use PhpLlm\LlmChain\Document\VectorInterface;
use PhpLlm\LlmChain\Exception\RuntimeException;
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
