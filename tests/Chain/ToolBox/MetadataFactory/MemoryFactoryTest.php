<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox\MetadataFactory;

use PhpLlm\LlmChain\Chain\JsonSchema\DescriptionParser;
use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolMetadataException;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory\MemoryFactory;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoAttribute1;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoAttribute2;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoryFactory::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ToolMetadataException::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
final class MemoryFactoryTest extends TestCase
{
    #[Test]
    public function getMetadataWithoutTools(): void
    {
        $this->expectException(ToolMetadataException::class);
        $this->expectExceptionMessage('The reference "SomeClass" is not a valid as tool.');

        $factory = new MemoryFactory();
        iterator_to_array($factory->getMetadata('SomeClass')); // @phpstan-ignore-line Yes, this class does not exist
    }

    #[Test]
    public function getMetadataWithDistinctToolPerClass(): void
    {
        $factory = (new MemoryFactory())
            ->addTool(ToolNoAttribute1::class, 'happy_birthday', 'Generates birthday message')
            ->addTool(ToolNoAttribute2::class, 'checkout', 'Buys a number of items per product', 'buy');

        $metadata = iterator_to_array($factory->getMetadata(ToolNoAttribute1::class));

        self::assertCount(1, $metadata);
        self::assertInstanceOf(Metadata::class, $metadata[0]);
        self::assertSame('happy_birthday', $metadata[0]->name);
        self::assertSame('Generates birthday message', $metadata[0]->description);
        self::assertSame('__invoke', $metadata[0]->reference->method);

        $expectedParams = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'description' => 'the name of the person'],
                'years' => ['type' => 'integer', 'description' => 'the age of the person'],
            ],
            'required' => ['name', 'years'],
            'additionalProperties' => false,
        ];

        self::assertSame($expectedParams, $metadata[0]->parameters);
    }

    #[Test]
    public function getMetadataWithMultipleToolsInClass(): void
    {
        $factory = (new MemoryFactory())
            ->addTool(ToolNoAttribute2::class, 'checkout', 'Buys a number of items per product', 'buy')
            ->addTool(ToolNoAttribute2::class, 'cancel', 'Cancels an order', 'cancel');

        $metadata = iterator_to_array($factory->getMetadata(ToolNoAttribute2::class));

        self::assertCount(2, $metadata);
        self::assertInstanceOf(Metadata::class, $metadata[0]);
        self::assertSame('checkout', $metadata[0]->name);
        self::assertSame('Buys a number of items per product', $metadata[0]->description);
        self::assertSame('buy', $metadata[0]->reference->method);

        $expectedParams = [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'the ID of the product'],
                'amount' => ['type' => 'integer', 'description' => 'the number of products'],
            ],
            'required' => ['id', 'amount'],
            'additionalProperties' => false,
        ];
        self::assertSame($expectedParams, $metadata[0]->parameters);

        self::assertInstanceOf(Metadata::class, $metadata[1]);
        self::assertSame('cancel', $metadata[1]->name);
        self::assertSame('Cancels an order', $metadata[1]->description);
        self::assertSame('cancel', $metadata[1]->reference->method);

        $expectedParams = [
            'type' => 'object',
            'properties' => [
                'orderId' => ['type' => 'string', 'description' => 'the ID of the order'],
            ],
            'required' => ['orderId'],
            'additionalProperties' => false,
        ];
        self::assertSame($expectedParams, $metadata[1]->parameters);
    }
}
