<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Fabric;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Platform\Fabric\FabricInputProcessor;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FabricInputProcessor::class)]
#[Small]
final class FabricInputProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithoutFabricPattern(): void
    {
        $processor = new FabricInputProcessor();

        $messages = new MessageBag();
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['temperature' => 0.7]);

        $processor->processInput($input);

        self::assertCount(0, $input->messages);
        self::assertSame(['temperature' => 0.7], $input->getOptions());
    }

    #[Test]
    public function processInputWithInvalidPatternType(): void
    {
        $processor = new FabricInputProcessor();

        $messages = new MessageBag();
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['fabric_pattern' => 123]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "fabric_pattern" option must be a string');

        $processor->processInput($input);
    }

    #[Test]
    public function processInputThrowsExceptionWhenSystemMessageExists(): void
    {
        $processor = new FabricInputProcessor();

        $messages = new MessageBag(new SystemMessage('Existing system message'));
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['fabric_pattern' => 'test_pattern']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot add Fabric pattern: MessageBag already contains a system message');

        $processor->processInput($input);
    }

    #[Test]
    public function processInputThrowsExceptionWhenPackageNotInstalled(): void
    {
        $processor = new FabricInputProcessor();

        $messages = new MessageBag();
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['fabric_pattern' => 'test_pattern']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package');

        $processor->processInput($input);
    }
}
