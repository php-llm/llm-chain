<?php

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\ToolCallArgumentResolver;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Tool\ExecutionReference;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolDate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolCallArgumentResolver::class)]
#[UsesClass(Tool::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(ToolCall::class)]
class ToolCallArgumentResolverTest extends TestCase
{
    #[Test]
    public function resolveArguments(): void
    {
        $resolver = new ToolCallArgumentResolver();

        $tool = new ToolDate();
        $metadata = new Tool(new ExecutionReference(ToolDate::class, '__invoke'), 'tool_date', 'test');
        $toolCall = new ToolCall('invocation', 'tool_date', ['date' => '2025-06-29']);

        self::assertEquals(['date' => new \DateTimeImmutable('2025-06-29')], $resolver->resolveArguments($tool, $metadata, $toolCall));
    }
}
