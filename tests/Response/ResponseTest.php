<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Response;

use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\Response;
use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Response::class)]
#[UsesClass(Choice::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ResponseTest extends TestCase
{
    public function testGetChoices(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
            new Choice('content', [new ToolCall('call_234567', 'name', ['foo' => 'bar'])]),
        );

        self::assertCount(2, $response->getChoices());
    }

    public function testConstructorThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response must have at least one choice');

        $response = new Response();
    }

    public function testGetContent(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        self::assertSame('content', $response->getContent());
    }

    public function testGetContentThrowsException(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response has more than one choice');

        $response->getContent();
    }

    public function testGetToolCalls(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        self::assertCount(1, $response->getToolCalls());
    }

    public function testGetToolCallsThrowsException(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response has more than one choice');

        $response->getToolCalls();
    }

    public function testHasToolCalls(): void
    {
        $response = new Response(
            new Choice('content', [new ToolCall('call_123456', 'name', ['foo' => 'bar'])]),
        );

        self::assertTrue($response->hasToolCalls());
    }

    public function testHasToolCallsReturnsFalse(): void
    {
        $response = new Response(new Choice('content'));

        self::assertFalse($response->hasToolCalls());
    }
}
