<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox;

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\ExecutionReference;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\LanguageModel;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(ChainProcessor::class)]
#[UsesClass(Input::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(MissingModelSupport::class)]
class ChainProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithoutRegisteredToolsWillResultInNoOptionChange(): void
    {
        $toolbox = $this->createStub(ToolboxInterface::class);
        $toolbox->method('getMap')->willReturn([]);

        $llm = self::createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(true);

        $chainProcessor = new ChainProcessor($toolbox);
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame([], $input->getOptions());
    }

    #[Test]
    public function processInputWithRegisteredToolsWillResultInOptionChange(): void
    {
        $toolbox = $this->createStub(ToolboxInterface::class);
        $tool1 = new Metadata(new ExecutionReference('ClassTool1', 'method1'), 'tool1', 'description1', null);
        $tool2 = new Metadata(new ExecutionReference('ClassTool2', 'method1'), 'tool2', 'description2', null);
        $toolbox->method('getMap')->willReturn([$tool1, $tool2]);

        $llm = self::createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(true);

        $chainProcessor = new ChainProcessor($toolbox);
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame(['tools' => [$tool1, $tool2]], $input->getOptions());
    }

    #[Test]
    public function processInputWithRegisteredToolsButToolOverride(): void
    {
        $toolbox = $this->createStub(ToolboxInterface::class);
        $tool1 = new Metadata(new ExecutionReference('ClassTool1', 'method1'), 'tool1', 'description1', null);
        $tool2 = new Metadata(new ExecutionReference('ClassTool2', 'method1'), 'tool2', 'description2', null);
        $toolbox->method('getMap')->willReturn([$tool1, $tool2]);

        $llm = self::createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(true);

        $chainProcessor = new ChainProcessor($toolbox);
        $input = new Input($llm, new MessageBag(), ['tools' => ['tool2']]);

        $chainProcessor->processInput($input);

        self::assertSame(['tools' => [$tool2]], $input->getOptions());
    }

    #[Test]
    public function processInputWithUnsupportedToolCallingWillThrowException(): void
    {
        self::expectException(MissingModelSupport::class);

        $llm = self::createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(false);

        $chainProcessor = new ChainProcessor($this->createStub(ToolboxInterface::class));
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);
    }

    #[Test]
    public function processOutputWithToolCallResponseKeepingMessages(): void
    {
        $toolbox = $this->createMock(ToolboxInterface::class);
        $toolbox->expects($this->once())->method('execute')->willReturn('Test response');

        $llm = $this->createStub(LanguageModel::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch');

        $messageBag = new MessageBag();

        $response = new ToolCallResponse(new ToolCall('id1', 'tool1', ['arg1' => 'value1']));

        $chain = new Chain($this->createStub(PlatformInterface::class), $llm);

        $chainProcessor = new ChainProcessor($toolbox, eventDispatcher: $eventDispatcher, keepToolMessages: true);
        $chainProcessor->setChain($chain);

        $output = new Output($llm, $response, $messageBag, []);

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

        $llm = $this->createStub(LanguageModel::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch');

        $messageBag = new MessageBag();

        $response = new ToolCallResponse(new ToolCall('id1', 'tool1', ['arg1' => 'value1']));

        $chain = new Chain($this->createStub(PlatformInterface::class), $llm);

        $chainProcessor = new ChainProcessor($toolbox, eventDispatcher: $eventDispatcher, keepToolMessages: false);
        $chainProcessor->setChain($chain);

        $output = new Output($llm, $response, $messageBag, []);

        $chainProcessor->processOutput($output);

        self::assertCount(0, $messageBag);
    }
}
