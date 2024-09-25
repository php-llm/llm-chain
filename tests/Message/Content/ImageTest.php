<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Tests\Message\Content;

use PhpLlm\LlmChain\Message\Content\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Image::class)]
#[Small]
final class ImageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new Image('foo');

        self::assertSame('foo', $obj->url);
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $obj = new Image('foo');

        self::assertSame(
            ['type' => 'image_url', 'image_url' => ['url' => 'foo']],
            $obj->jsonSerialize(),
        );
    }
}
