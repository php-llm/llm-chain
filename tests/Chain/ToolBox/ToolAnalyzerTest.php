<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\JsonSchema\DescriptionParser;
use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMultiple;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolWrong;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolAnalyzer::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(ToolConfigurationException::class)]
final class ToolAnalyzerTest extends TestCase
{
    private ToolAnalyzer $toolAnalyzer;

    protected function setUp(): void
    {
        $this->toolAnalyzer = new ToolAnalyzer();
    }

    #[Test]
    public function withoutAttribute(): void
    {
        $this->expectException(ToolConfigurationException::class);
        iterator_to_array($this->toolAnalyzer->getMetadata(ToolWrong::class));
    }

    #[Test]
    public function getDefinition(): void
    {
        /** @var Metadata[] $metadatas */
        $metadatas = iterator_to_array($this->toolAnalyzer->getMetadata(ToolRequiredParams::class));

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
        $metadatas = iterator_to_array($this->toolAnalyzer->getMetadata(ToolMultiple::class));

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
        self::assertSame($className, $metadata->className);
        self::assertSame($name, $metadata->name);
        self::assertSame($description, $metadata->description);
        self::assertSame($method, $metadata->method);
        self::assertSame($parameters, $metadata->parameters);
    }
}
