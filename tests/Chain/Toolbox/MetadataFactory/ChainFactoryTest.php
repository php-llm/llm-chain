<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox\MetadataFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolException;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\ChainFactory;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\MemoryToolFactory;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\ReflectionToolFactory;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMisconfigured;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMultiple;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoAttribute1;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChainFactory::class)]
#[Medium]
#[UsesClass(MemoryToolFactory::class)]
#[UsesClass(ReflectionToolFactory::class)]
#[UsesClass(ToolException::class)]
final class ChainFactoryTest extends TestCase
{
    private ChainFactory $factory;

    protected function setUp(): void
    {
        $factory1 = (new MemoryToolFactory())
            ->addTool(ToolNoAttribute1::class, 'reference', 'A reference tool')
            ->addTool(ToolOptionalParam::class, 'optional_param', 'Tool with optional param', 'bar');
        $factory2 = new ReflectionToolFactory();

        $this->factory = new ChainFactory([$factory1, $factory2]);
    }

    #[Test]
    public function testGetMetadataNotExistingClass(): void
    {
        self::expectException(ToolException::class);
        self::expectExceptionMessage('The reference "NoClass" is not a valid tool.');

        iterator_to_array($this->factory->getTool('NoClass'));
    }

    #[Test]
    public function testGetMetadataNotConfiguredClass(): void
    {
        self::expectException(ToolConfigurationException::class);
        self::expectExceptionMessage(\sprintf('Method "foo" not found in tool "%s".', ToolMisconfigured::class));

        iterator_to_array($this->factory->getTool(ToolMisconfigured::class));
    }

    #[Test]
    public function testGetMetadataWithAttributeSingleHit(): void
    {
        $metadata = iterator_to_array($this->factory->getTool(ToolRequiredParams::class));

        self::assertCount(1, $metadata);
    }

    #[Test]
    public function testGetMetadataOverwrite(): void
    {
        $metadata = iterator_to_array($this->factory->getTool(ToolOptionalParam::class));

        self::assertCount(1, $metadata);
        self::assertSame('optional_param', $metadata[0]->name);
        self::assertSame('Tool with optional param', $metadata[0]->description);
        self::assertSame('bar', $metadata[0]->reference->method);
    }

    #[Test]
    public function testGetMetadataWithAttributeDoubleHit(): void
    {
        $metadata = iterator_to_array($this->factory->getTool(ToolMultiple::class));

        self::assertCount(2, $metadata);
    }

    #[Test]
    public function testGetMetadataWithMemorySingleHit(): void
    {
        $metadata = iterator_to_array($this->factory->getTool(ToolNoAttribute1::class));

        self::assertCount(1, $metadata);
    }
}
