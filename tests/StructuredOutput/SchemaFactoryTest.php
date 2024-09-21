<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\StructuredOutput;

use PhpLlm\LlmChain\StructuredOutput\SchemaFactory;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\MathReasoning;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\Step;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaFactory::class)]
final class SchemaFactoryTest extends TestCase
{
    private SchemaFactory $schemaFactory;

    protected function setUp(): void
    {
        $this->schemaFactory = SchemaFactory::create();
    }

    public function testBuildSchemaForUserClass(): void
    {
        $expected = [
            'title' => 'User',
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'createdAt' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'isActive' => ['type' => 'boolean'],
            ],
            'required' => ['id', 'name', 'createdAt', 'isActive'],
            'additionalProperties' => false,
        ];

        $actual = $this->schemaFactory->buildSchema(User::class);

        self::assertSame($expected, $actual);
    }

    public function testBuildSchemaForMathReasoningClass(): void
    {
        $expected = [
            'title' => 'MathReasoning',
            'type' => 'object',
            'properties' => [
                'steps' => [
                    'type' => 'array',
                    'items' => [
                        'title' => 'Step',
                        'type' => 'object',
                        'properties' => [
                            'explanation' => ['type' => 'string'],
                            'output' => ['type' => 'string'],
                        ],
                        'required' => ['explanation', 'output'],
                        'additionalProperties' => false,
                    ],
                ],
                'finalAnswer' => ['type' => 'string'],
            ],
            'required' => ['steps', 'finalAnswer'],
            'additionalProperties' => false,
        ];

        $actual = $this->schemaFactory->buildSchema(MathReasoning::class);

        self::assertSame($expected, $actual);
    }

    public function testBuildSchemaForStepClass(): void
    {
        $expected = [
            'title' => 'Step',
            'type' => 'object',
            'properties' => [
                'explanation' => ['type' => 'string'],
                'output' => ['type' => 'string'],
            ],
            'required' => ['explanation', 'output'],
            'additionalProperties' => false,
        ];

        $actual = $this->schemaFactory->buildSchema(Step::class);

        self::assertSame($expected, $actual);
    }
}
