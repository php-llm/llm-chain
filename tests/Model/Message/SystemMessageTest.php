<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message;

use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemMessage::class)]
#[Small]
final class SystemMessageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $message = new SystemMessage('foo');

        self::assertSame(Role::System, $message->getRole());
        self::assertSame('foo', $message->content);
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $systemMessage = new SystemMessage('foo');

        self::assertSame(['role' => Role::System, 'content' => 'foo'], $systemMessage->jsonSerialize());
    }
}
