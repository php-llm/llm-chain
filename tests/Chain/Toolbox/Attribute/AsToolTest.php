<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox\Attribute;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsTool::class)]
final class AsToolTest extends TestCase
{
    #[Test]
    public function canBeConstructed(): void
    {
        $attribute = new AsTool(
            name: 'name',
            description: 'description',
        );

        self::assertSame('name', $attribute->name);
        self::assertSame('description', $attribute->description);
    }
}
