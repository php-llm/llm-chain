<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\ChainInterface;
use PhpLlm\LlmChain\Chain\Exception\MissingModelSupportException;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\Tool\ExecutionReference;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChainProcessor::class)]
#[UsesClass(Input::class)]
#[UsesClass(Output::class)]
#[UsesClass(Tool::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(ToolCallResponse::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(MissingModelSupportException::class)]
#[UsesClass(Model::class)]
class ChainProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithoutRegisteredToolsWillResultInNoOptionChange(): void
    {
        $toolbox = $this->createStub(ToolboxInterface::class);
        $toolbox->method('getTools')->willReturn([]);

        $model = new Model('gpt-4', [Capability::TOOL_CALLING]);
        $chainProcessor = new ChainProcessor($toolbox);
        $input = new Input($model, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame([], $input->getOptions());
    }

    #[Test]
    public function processInputWithRegisteredToolsWillResultInOptionChange(): void
    {
        $toolbox = $this->createStub(ToolboxInterface::class);
        $tool1 = new Tool(new ExecutionReference('ClassTool1', 'method1'), 'tool1', 'description1', null);
        $tool2 = new Tool(new ExecutionReference('ClassTool2', 'method1'), 'tool2', 'description2', null);
        $toolbox->method('getTools')->willReturn([$tool1, $tool2]);

        $model = new Model('gpt-4', [Capability::TOOL_CALLING]);
        $chainProcessor = new ChainProcessor($toolbox);
        $input = new Input($model, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame(['tools' => [$tool1, $tool2]], $input->getOptions());
    }

    #[Test]
    public function processInputWithRegisteredToolsButToolOverride(): void
    {
        $toolbox = $this->createStub(ToolboxInterface::class);
        $tool1 = new Tool(new ExecutionReference('ClassTool1', 'method1'), 'tool1', 'description1', null);
        $tool2 = new Tool(new ExecutionReference('ClassTool2', 'method1'), 'tool2', 'description2', null);
        $toolbox->method('getTools')->willReturn([$tool1, $tool2]);

        $model = new Model('gpt-4', [Capability::TOOL_CALLING]);
        $chainProcessor = new ChainProcessor($toolbox);
        $input = new Input($model, new MessageBag(), ['tools' => ['tool2']]);

        $chainProcessor->processInput($input);

        self::assertSame(['tools' => [$tool2]], $input->getOptions());
    }

    #[Test]
    public function processInputWithUnsupportedToolCallingWillThrowException(): void
    {
        self::expectException(MissingModelSupportException::class);

        $model = new Model('gpt-3');
        $chainProcessor = new ChainProcessor($this->createStub(ToolboxInterface::class));
        $input = new Input($model, new MessageBag(), []);

        $chainProcessor->processInput($input);
    }

    #[Test]
    public function processOutputWithToolCallResponseKeepingMessages(): void
    {
        $toolbox = $this->createMock(ToolboxInterface::class);
        $toolbox->expects($this->once())->method('execute')->willReturn('Test response');

        $model = new Model('gpt-4', [Capability::TOOL_CALLING]);

        $messageBag = new MessageBag();

        $response = new ToolCallResponse(new ToolCall('id1', 'tool1', ['arg1' => 'value1']));

        $chain = $this->createStub(ChainInterface::class);

        $chainProcessor = new ChainProcessor($toolbox, keepToolMessages: true);
        $chainProcessor->setChain($chain);

        $output = new Output($model, $response, $messageBag, []);

        $chainProcessor->processOutput($output);

        self::assertCount(2, $messageBag);
        self::assertInstanceOf(AssistantMessage::class, $messageBag->getMessages()[0]);
        self::assertInstanceOf(ToolCallMessage::class, $messageBag->getMessages()[1]);
    }

    #[Test]
    public function processOutputWithToolCallResponseForgettingMessages(): void
    {
        $toolbox = $this->createMock(ToolboxInterface::class);
        $toolbox->expects($this->once())->method('execute')->willReturn('Test response');

        $model = new Model('gpt-4', [Capability::TOOL_CALLING]);

        $messageBag = new MessageBag();

        $response = new ToolCallResponse(new ToolCall('id1', 'tool1', ['arg1' => 'value1']));

        $chain = $this->createStub(ChainInterface::class);

        $chainProcessor = new ChainProcessor($toolbox, keepToolMessages: false);
        $chainProcessor->setChain($chain);

        $output = new Output($model, $response, $messageBag, []);

        $chainProcessor->processOutput($output);

        self::assertCount(0, $messageBag);
    }
}
