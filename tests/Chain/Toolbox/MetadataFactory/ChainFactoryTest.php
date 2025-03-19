<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox\MetadataFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolMetadataException;
use PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory\ChainFactory;
use PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory\MemoryFactory;
use PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory\ReflectionFactory;
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
#[UsesClass(MemoryFactory::class)]
#[UsesClass(ReflectionFactory::class)]
#[UsesClass(ToolMetadataException::class)]
final class ChainFactoryTest extends TestCase
{
    private ChainFactory $factory;

    protected function setUp(): void
    {
        $factory1 = (new MemoryFactory())
            ->addTool(ToolNoAttribute1::class, 'reference', 'A reference tool')
            ->addTool(ToolOptionalParam::class, 'optional_param', 'Tool with optional param', 'bar');
        $factory2 = new ReflectionFactory();

        $this->factory = new ChainFactory([$factory1, $factory2]);
    }

    #[Test]
    public function testGetMetadataNotExistingClass(): void
    {
        $this->expectException(ToolMetadataException::class);
        $this->expectExceptionMessage('The reference "NoClass" is not a valid as tool.');

        iterator_to_array($this->factory->getMetadata('NoClass'));
    }

    #[Test]
    public function testGetMetadataNotConfiguredClass(): void
    {
        $this->expectException(ToolConfigurationException::class);
        $this->expectExceptionMessage(sprintf('Method "foo" not found in tool "%s".', ToolMisconfigured::class));

        iterator_to_array($this->factory->getMetadata(ToolMisconfigured::class));
    }

    #[Test]
    public function testGetMetadataWithAttributeSingleHit(): void
    {
        $metadata = iterator_to_array($this->factory->getMetadata(ToolRequiredParams::class));

        self::assertCount(1, $metadata);
    }

    #[Test]
    public function testGetMetadataOverwrite(): void
    {
        $metadata = iterator_to_array($this->factory->getMetadata(ToolOptionalParam::class));

        self::assertCount(1, $metadata);
        self::assertSame('optional_param', $metadata[0]->name);
        self::assertSame('Tool with optional param', $metadata[0]->description);
        self::assertSame('bar', $metadata[0]->reference->method);
    }

    #[Test]
    public function testGetMetadataWithAttributeDoubleHit(): void
    {
        $metadata = iterator_to_array($this->factory->getMetadata(ToolMultiple::class));

        self::assertCount(2, $metadata);
    }

    #[Test]
    public function testGetMetadataWithMemorySingleHit(): void
    {
        $metadata = iterator_to_array($this->factory->getMetadata(ToolNoAttribute1::class));

        self::assertCount(1, $metadata);
    }
}
