<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Chain\ToolBox\FaultTolerantToolBox;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBoxInterface;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FaultTolerantToolBox::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ToolNotFoundException::class)]
#[UsesClass(ToolExecutionException::class)]
final class FaultTolerantToolBoxTest extends TestCase
{
    #[Test]
    public function faultyToolExecution(): void
    {
        $faultyToolBox = $this->createFaultyToolBox(
            fn (ToolCall $toolCall) => ToolExecutionException::executionFailed($toolCall, new \Exception('error'))
        );

        $faultTolerantToolBox = new FaultTolerantToolBox($faultyToolBox);
        $expected = 'An error occurred while executing tool "tool_foo".';

        $toolCall = new ToolCall('987654321', 'tool_foo');
        $actual = $faultTolerantToolBox->execute($toolCall);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function faultyToolCall(): void
    {
        $faultyToolBox = $this->createFaultyToolBox(
            fn (ToolCall $toolCall) => ToolNotFoundException::notFoundForToolCall($toolCall)
        );

        $faultTolerantToolBox = new FaultTolerantToolBox($faultyToolBox);
        $expected = 'Tool "tool_xyz" was not found, please use one of these: tool_no_params, tool_required_params';

        $toolCall = new ToolCall('123456789', 'tool_xyz');
        $actual = $faultTolerantToolBox->execute($toolCall);

        self::assertSame($expected, $actual);
    }

    private function createFaultyToolBox(\Closure $exceptionFactory): ToolBoxInterface
    {
        return new class($exceptionFactory) implements ToolBoxInterface {
            public function __construct(private readonly \Closure $exceptionFactory)
            {
            }

            /**
             * @return Metadata[]
             */
            public function getMap(): array
            {
                return [
                    new Metadata('tool_no_params', 'A tool without parameters', '__invoke', null),
                    new Metadata('tool_required_params', 'A tool with required parameters', 'bar', null),
                ];
            }

            public function execute(ToolCall $toolCall): mixed
            {
                throw ($this->exceptionFactory)($toolCall);
            }
        };
    }
}
