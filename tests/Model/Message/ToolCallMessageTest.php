<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Message;

use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolCallMessage::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ToolCallMessageTest extends TestCase
{
    #[Test]
    public function constructionIsPossible(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $obj = new ToolCallMessage($toolCall, 'bar');

        self::assertSame($toolCall, $obj->toolCall);
        self::assertSame('bar', $obj->content);
    }

    #[Test]
    public function jsonConversionIsWorkingAsExpected(): void
    {
        $toolCall = new ToolCall('foo', 'bar');
        $obj = new ToolCallMessage($toolCall, 'bar');

        self::assertSame(['role' => Role::ToolCall, 'content' => 'bar', 'tool_call_id' => 'foo'], $obj->jsonSerialize());
    }
}
