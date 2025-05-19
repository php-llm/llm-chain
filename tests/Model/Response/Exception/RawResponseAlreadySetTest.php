<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Response\Exception;

use PhpLlm\LlmChain\Model\Response\Exception\RawResponseAlreadySet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RawResponseAlreadySet::class)]
#[Small]
final class RawResponseAlreadySetTest extends TestCase
{
    #[Test]
    public function itHasCorrectExceptionMessage(): void
    {
        $exception = new RawResponseAlreadySet();

        self::assertSame('The raw response was already set.', $exception->getMessage());
    }
}
