<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\JsonSchema;

use PhpLlm\LlmChain\Chain\JsonSchema\DescriptionParser;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\User;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\UserWithConstructor;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolWithoutDocs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DescriptionParser::class)]
final class DescriptionParserTest extends TestCase
{
    #[Test]
    public function fromPropertyWithoutDocBlock(): void
    {
        $property = new \ReflectionProperty(User::class, 'id');

        $actual = (new DescriptionParser())->getDescription($property);

        self::assertSame('', $actual);
    }

    #[Test]
    public function fromPropertyWithDocBlock(): void
    {
        $property = new \ReflectionProperty(User::class, 'name');

        $actual = (new DescriptionParser())->getDescription($property);

        self::assertSame('The name of the user in lowercase', $actual);
    }

    #[Test]
    public function fromPropertyWithConstructorDocBlock(): void
    {
        $property = new \ReflectionProperty(UserWithConstructor::class, 'name');

        $actual = (new DescriptionParser())->getDescription($property);

        self::assertSame('The name of the user in lowercase', $actual);
    }

    #[Test]
    public function fromParameterWithoutDocBlock(): void
    {
        $parameter = new \ReflectionParameter([ToolWithoutDocs::class, 'bar'], 'text');

        $actual = (new DescriptionParser())->getDescription($parameter);

        self::assertSame('', $actual);
    }

    #[Test]
    public function fromParameterWithDocBlock(): void
    {
        $parameter = new \ReflectionParameter([ToolRequiredParams::class, 'bar'], 'text');

        $actual = (new DescriptionParser())->getDescription($parameter);

        self::assertSame('The text given to the tool', $actual);
    }

    #[Test]
    #[DataProvider('provideMethodDescriptionCases')]
    public function fromParameterWithDocs(string $comment, string $expected): void
    {
        $method = self::createMock(\ReflectionMethod::class);
        $method->method('getDocComment')->willReturn($comment);
        $parameter = self::createMock(\ReflectionParameter::class);
        $parameter->method('getDeclaringFunction')->willReturn($method);
        $parameter->method('getName')->willReturn('myParam');

        $actual = (new DescriptionParser())->getDescription($parameter);

        self::assertSame($expected, $actual);
    }

    public static function provideMethodDescriptionCases(): \Generator
    {
        yield 'empty doc block' => [
            'comment' => '',
            'expected' => '',
        ];

        yield 'single line doc block with description' => [
            'comment' => '/** @param string $myParam The description */',
            'expected' => 'The description',
        ];

        yield 'multi line doc block with description and other tags' => [
            'comment' => <<<'TEXT'
                    /**
                     * @param string $myParam The description
                     * @return void
                     */
                TEXT,
            'expected' => 'The description',
        ];

        yield 'multi line doc block with multiple parameters' => [
            'comment' => <<<'TEXT'
                    /**
                     * @param string $myParam The description
                     * @param string $anotherParam The wrong description
                     */
                TEXT,
            'expected' => 'The description',
        ];

        yield 'multi line doc block with parameter that is not searched for' => [
            'comment' => <<<'TEXT'
                    /**
                     * @param string $unknownParam The description
                     */
                TEXT,
            'expected' => '',
        ];
    }
}
