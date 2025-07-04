<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Fabric;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Platform\Fabric\Exception\PatternNotFoundException;
use PhpLlm\LlmChain\Platform\Fabric\FabricInputProcessor;
use PhpLlm\LlmChain\Platform\Fabric\FabricPrompt;
use PhpLlm\LlmChain\Platform\Fabric\FabricRepository;
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
    public function processInputWithFabricPattern(): void
    {
        $repository = $this->createMock(FabricRepository::class);
        $repository->expects($this->once())
            ->method('load')
            ->with('test_pattern')
            ->willReturn(new FabricPrompt('test_pattern', '# Fabric Content'));

        $processor = new FabricInputProcessor($repository);

        $messages = new MessageBag();
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['fabric_pattern' => 'test_pattern']);

        $processor->processInput($input);

        self::assertCount(1, $input->messages);
        $messages = $input->messages->getMessages();
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertSame('# Fabric Content', $messages[0]->content);
        self::assertArrayNotHasKey('fabric_pattern', $input->getOptions());
    }

    #[Test]
    public function processInputWithoutFabricPattern(): void
    {
        $repository = $this->createMock(FabricRepository::class);
        $repository->expects($this->never())->method('load');

        $processor = new FabricInputProcessor($repository);

        $messages = new MessageBag();
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['temperature' => 0.7]);

        $processor->processInput($input);

        self::assertCount(0, $input->messages);
        self::assertSame(['temperature' => 0.7], $input->getOptions());
    }

    #[Test]
    public function processInputWithDefaultRepositoryThrowsExceptionWhenPackageNotInstalled(): void
    {
        $processor = new FabricInputProcessor();

        $messages = new MessageBag();
        $model = new Model('test-model', []);
        $input = new Input($model, $messages, ['fabric_pattern' => 'test_pattern']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package');

        $processor->processInput($input);
    }

    #[Test]
    public function processInputWithCustomPatternsPath(): void
    {
        $testPath = sys_get_temp_dir().'/fabric-test-'.uniqid();
        mkdir($testPath.'/test_pattern', 0777, true);
        file_put_contents($testPath.'/test_pattern/system.md', '# Custom Path Content');

        $repository = new FabricRepository($testPath);
        $processor = new FabricInputProcessor($repository);

        $messages = new MessageBag();
        $model = new Model('test-model', []);

        $input = new Input($model, $messages, [
            'fabric_pattern' => 'test_pattern',
        ]);

        try {
            $processor->processInput($input);

            self::assertCount(1, $input->messages);
            $messages = $input->messages->getMessages();
            self::assertInstanceOf(SystemMessage::class, $messages[0]);
            self::assertSame('# Custom Path Content', $messages[0]->content);
            self::assertArrayNotHasKey('fabric_pattern', $input->getOptions());
        } finally {
            // Cleanup
            unlink($testPath.'/test_pattern/system.md');
            rmdir($testPath.'/test_pattern');
            rmdir($testPath);
        }
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
    public function processInputPatternNotFound(): void
    {
        $repository = $this->createMock(FabricRepository::class);
        $repository->expects($this->once())
            ->method('load')
            ->with('non_existing')
            ->willThrowException(new PatternNotFoundException('Pattern not found'));

        $processor = new FabricInputProcessor($repository);

        $messages = new MessageBag();
        $model = new Model('test-model');
        $input = new Input($model, $messages, ['fabric_pattern' => 'non_existing']);

        $this->expectException(PatternNotFoundException::class);

        $processor->processInput($input);
    }
}
