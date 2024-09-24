<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Message\Content;

use PhpLlm\LlmChain\Message\Content\TextContent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextContent::class)]
#[Small]
final class TextContentTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new TextContent('foo');

        self::assertSame('foo', $obj->text);
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $obj = new TextContent('foo');

        self::assertSame(
            ['type' => 'text', 'text' => 'foo'],
            $obj->jsonSerialize(),
        );
    }
}
