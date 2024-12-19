<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message;

use PhpLlm\LlmChain\Model\Message\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Role::class)]
#[Small]
final class RoleTest extends TestCase
{
    #[Test]
    public function values(): void
    {
        self::assertSame('system', Role::System->value);
        self::assertSame('assistant', Role::Assistant->value);
        self::assertSame('user', Role::User->value);
        self::assertSame('tool', Role::ToolCall->value);
    }

    #[Test]
    public function equals(): void
    {
        self::assertTrue(Role::System->equals(Role::System));
    }

    #[Test]
    public function notEquals(): void
    {
        self::assertTrue(Role::System->notEquals(Role::Assistant));
    }

    #[Test]
    public function notEqualsOneOf(): void
    {
        self::assertTrue(Role::System->notEqualsOneOf([Role::Assistant, Role::User]));
    }

    #[Test]
    public function equalsOneOf(): void
    {
        self::assertTrue(Role::System->equalsOneOf([Role::System, Role::User]));
    }
}
