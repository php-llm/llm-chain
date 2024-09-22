<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Response;

use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\Response;
use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Response::class)]
#[UsesClass(Choice::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ResponseTest extends TestCase
{
    #[Test]
    public function getChoices(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
            new Choice('content', [new ToolCall('call_234567', 'name', ['foo' => 'bar'])]),
        );

        self::assertCount(2, $response->getChoices());
    }

    #[Test]
    public function constructorThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response must have at least one choice');

        $response = new Response();
    }

    #[Test]
    public function getContent(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        self::assertSame('content', $response->getContent());
    }

    #[Test]
    public function getContentThrowsException(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response has more than one choice');

        $response->getContent();
    }

    #[Test]
    public function getToolCalls(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        self::assertCount(1, $response->getToolCalls());
    }

    #[Test]
    public function getToolCallsThrowsException(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response has more than one choice');

        $response->getToolCalls();
    }

    #[Test]
    public function hasToolCalls(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        self::assertTrue($response->hasToolCalls());
    }

    #[Test]
    public function hasToolCallsReturnsFalse(): void
    {
        $response = new Response(new Choice('content'));

        self::assertFalse($response->hasToolCalls());
    }
}
