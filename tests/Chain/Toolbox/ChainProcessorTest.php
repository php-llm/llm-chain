<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\ExecutionReference;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
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
}
