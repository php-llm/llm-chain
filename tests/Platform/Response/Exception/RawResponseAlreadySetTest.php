<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response\Exception;

use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RawResponseAlreadySetException::class)]
#[Small]
final class RawResponseAlreadySetTest extends TestCase
{
    #[Test]
    public function itHasCorrectExceptionMessage(): void
    {
        $exception = new RawResponseAlreadySetException();

        self::assertSame('The raw response was already set.', $exception->getMessage());
    }
}
