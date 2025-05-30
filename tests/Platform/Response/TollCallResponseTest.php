<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response;

use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolCallResponse::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class TollCallResponseTest extends TestCase
{
    #[Test]
    public function throwsIfNoToolCall(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Response must have at least one tool call.');

        new ToolCallResponse();
    }

    #[Test]
    public function getContent(): void
    {
        $response = new ToolCallResponse($toolCall = new ToolCall('ID', 'name', ['foo' => 'bar']));
        self::assertSame([$toolCall], $response->getContent());
    }
}
