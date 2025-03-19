<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox\MetadataFactory;

use PhpLlm\LlmChain\Chain\JsonSchema\DescriptionParser;
use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory\ReflectionFactory;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMultiple;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolWrong;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReflectionFactory::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(ToolConfigurationException::class)]
final class ReflectionFactoryTest extends TestCase
{
    private ReflectionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ReflectionFactory();
    }

    #[Test]
    public function invalidReferenceNonExistingClass(): void
    {
        $this->expectException(ToolConfigurationException::class);
        iterator_to_array($this->factory->getMetadata('invalid'));
    }

    #[Test]
    public function invalidReferenceNonInteger(): void
    {
        $this->expectException(ToolConfigurationException::class);
        iterator_to_array($this->factory->getMetadata(1234));
    }

    #[Test]
    public function invalidReferenceCallable(): void
    {
        $this->expectException(ToolConfigurationException::class);
        iterator_to_array($this->factory->getMetadata(fn () => null));
    }

    #[Test]
    public function withoutAttribute(): void
    {
        $this->expectException(ToolConfigurationException::class);
        iterator_to_array($this->factory->getMetadata(ToolWrong::class));
    }

    #[Test]
    public function getDefinition(): void
    {
        /** @var Metadata[] $metadatas */
        $metadatas = iterator_to_array($this->factory->getMetadata(ToolRequiredParams::class));

        self::assertToolConfiguration(
            metadata: $metadatas[0],
            className: ToolRequiredParams::class,
            name: 'tool_required_params',
            description: 'A tool with required parameters',
            method: 'bar',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'The text given to the tool',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'A number given to the tool',
                    ],
                ],
                'required' => ['text', 'number'],
                'additionalProperties' => false,
            ],
        );
    }

    #[Test]
    public function getDefinitionWithMultiple(): void
    {
        $metadatas = iterator_to_array($this->factory->getMetadata(ToolMultiple::class));

        self::assertCount(2, $metadatas);

        [$first, $second] = $metadatas;

        self::assertToolConfiguration(
            metadata: $first,
            className: ToolMultiple::class,
            name: 'tool_hello_world',
            description: 'Function to say hello',
            method: 'hello',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'world' => [
                        'type' => 'string',
                        'description' => 'The world to say hello to',
                    ],
                ],
                'required' => ['world'],
                'additionalProperties' => false,
            ],
        );

        self::assertToolConfiguration(
            metadata: $second,
            className: ToolMultiple::class,
            name: 'tool_required_params',
            description: 'Function to say a number',
            method: 'bar',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'The text given to the tool',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'A number given to the tool',
                    ],
                ],
                'required' => ['text', 'number'],
                'additionalProperties' => false,
            ],
        );
    }

    private function assertToolConfiguration(Metadata $metadata, string $className, string $name, string $description, string $method, array $parameters): void
    {
        self::assertSame($className, $metadata->reference->class);
        self::assertSame($method, $metadata->reference->method);
        self::assertSame($name, $metadata->name);
        self::assertSame($description, $metadata->description);
        self::assertSame($parameters, $metadata->parameters);
    }
}
