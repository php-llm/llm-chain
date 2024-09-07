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
        ];

        $schemaFactory = new SchemaFactory();
        $actual = $schemaFactory->buildSchema(User::class);

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
                    ],
                ],
                'finalAnswer' => ['type' => 'string'],
            ],
            'required' => ['steps', 'finalAnswer'],
        ];

        $schemaFactory = new SchemaFactory();
        $actual = $schemaFactory->buildSchema(MathReasoning::class);

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
        ];

        $schemaFactory = new SchemaFactory();
        $actual = $schemaFactory->buildSchema(Step::class);

        self::assertSame($expected, $actual);
    }
}
