<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\JsonSchema;

use PhpLlm\LlmChain\Chain\JsonSchema\TypeSchemaExtractor;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\MathReasoning;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\User;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolArray;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeSchemaExtractor::class)]
final class TypeSchemaExtractorTest extends TestCase
{
    #[Test]
    #[DataProvider('provideClassProperties')]
    public function testFromProperty(string $class, string $property, array $expected): void
    {
        $property = new \ReflectionProperty($class, $property);

        $actual = (new TypeSchemaExtractor())->fromProperty($property);

        self::assertSame($expected, $actual);
    }

    public static function provideClassProperties(): \Iterator
    {
        yield 'user_id' => [User::class, 'id', ['type' => 'integer']];
        yield 'user_name' => [User::class, 'name', ['type' => 'string']];
        yield 'user_createdAt' => [User::class, 'createdAt', ['type' => 'string', 'format' => 'date-time']];
        yield 'user_isActive' => [User::class, 'isActive', ['type' => 'boolean']];
        yield 'user_age' => [User::class, 'age', ['type' => 'integer']];
        yield 'reasoning_steps' => [
            MathReasoning::class, 'steps', [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'explanation' => ['type' => 'string'],
                        'output' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('provideMethodParameters')]
    public function testFromParameter(string $class, string $method, string $parameter, array $expected): void
    {
        $parameter = new \ReflectionParameter([$class, $method], $parameter);

        $actual = (new TypeSchemaExtractor())->fromParameter($parameter);

        self::assertSame($expected, $actual);
    }

    public static function provideMethodParameters(): \Iterator
    {
        yield 'tool_required_text' => [ToolRequiredParams::class, 'bar', 'text', ['type' => 'string']];
        yield 'tool_required_int' => [ToolRequiredParams::class, 'bar', 'number', ['type' => 'integer']];
        yield 'tool_optional_text' => [ToolOptionalParam::class, 'bar', 'text', ['type' => 'string']];
        yield 'tool_optional_int' => [ToolOptionalParam::class, 'bar', 'number', ['type' => 'integer']];
        yield 'tool_array_strings' => [ToolArray::class, '__invoke', 'urls', ['type' => 'array', 'items' => ['type' => 'string']]];
        yield 'tool_array_ints' => [ToolArray::class, '__invoke', 'ids', ['type' => 'array', 'items' => ['type' => 'integer']]];
    }
}
