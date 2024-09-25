<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Tests\StructuredOutput;

use PhpLlm\LlmChain\StructuredOutput\SchemaFactory;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\MathReasoning;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\Step;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaFactory::class)]
final class SchemaFactoryTest extends TestCase
{
    private SchemaFactory $schemaFactory;

    protected function setUp(): void
    {
        $this->schemaFactory = SchemaFactory::create();
    }

    #[Test]
    public function buildSchemaForUserClass(): void
    {
        $expected = [
            'title' => 'User',
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => [
                    'type' => 'string',
                    'description' => 'The name of the user in lowercase',
                ],
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

    #[Test]
    public function buildSchemaForMathReasoningClass(): void
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

    #[Test]
    public function buildSchemaForStepClass(): void
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
