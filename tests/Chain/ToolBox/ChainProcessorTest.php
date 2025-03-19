<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\ExecutionReference;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBoxInterface;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\LanguageModel;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChainProcessor::class)]
#[UsesClass(Input::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(MissingModelSupport::class)]
class ChainProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithoutRegisteredToolsWillResultInNoOptionChange(): void
    {
        $toolBox = $this->createStub(ToolBoxInterface::class);
        $toolBox->method('getMap')->willReturn([]);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(true);

        $chainProcessor = new ChainProcessor($toolBox);
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame([], $input->getOptions());
    }

    #[Test]
    public function processInputWithRegisteredToolsWillResultInOptionChange(): void
    {
        $toolBox = $this->createStub(ToolBoxInterface::class);
        $tool1 = new Metadata(new ExecutionReference('ClassTool1', 'method1'), 'tool1', 'description1', null);
        $tool2 = new Metadata(new ExecutionReference('ClassTool2', 'method1'), 'tool2', 'description2', null);
        $toolBox->method('getMap')->willReturn([$tool1, $tool2]);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(true);

        $chainProcessor = new ChainProcessor($toolBox);
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame(['tools' => [$tool1, $tool2]], $input->getOptions());
    }

    #[Test]
    public function processInputWithRegisteredToolsButToolOverride(): void
    {
        $toolBox = $this->createStub(ToolBoxInterface::class);
        $tool1 = new Metadata(new ExecutionReference('ClassTool1', 'method1'), 'tool1', 'description1', null);
        $tool2 = new Metadata(new ExecutionReference('ClassTool2', 'method1'), 'tool2', 'description2', null);
        $toolBox->method('getMap')->willReturn([$tool1, $tool2]);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(true);

        $chainProcessor = new ChainProcessor($toolBox);
        $input = new Input($llm, new MessageBag(), ['tools' => ['tool2']]);

        $chainProcessor->processInput($input);

        self::assertSame(['tools' => [$tool2]], $input->getOptions());
    }

    #[Test]
    public function processInputWithUnsupportedToolCallingWillThrowException(): void
    {
        $this->expectException(MissingModelSupport::class);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsToolCalling')->willReturn(false);

        $chainProcessor = new ChainProcessor($this->createStub(ToolBoxInterface::class));
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);
    }
}
