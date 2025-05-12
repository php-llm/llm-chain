<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message\Content;

use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Text::class)]
#[Small]
final class TextTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new Text('foo');

        self::assertSame('foo', $obj->text);
    }
}
