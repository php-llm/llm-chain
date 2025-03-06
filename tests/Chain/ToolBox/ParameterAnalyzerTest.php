<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\With;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolWithToolParameterAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParameterAnalyzer::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ParameterAnalyzer::class)]
#[UsesClass(With::class)]
final class ParameterAnalyzerTest extends TestCase
{
    private ParameterAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new ParameterAnalyzer();
    }

    #[Test]
    public function detectParameterDefinitionRequired(): void
    {
        $actual = $this->analyzer->getDefinition(ToolRequiredParams::class, 'bar');
        $expected = [
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
            'required' => [
                'text',
                'number',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function detectParameterDefinitionRequiredWithAdditionalToolParameterAttribute(): void
    {
        $actual = $this->analyzer->getDefinition(ToolWithToolParameterAttribute::class, '__invoke');
        $expected = [
            'type' => 'object',
            'properties' => [
                'animal' => [
                    'type' => 'string',
                    'description' => 'The animal given to the tool',
                    'enum' => ['dog', 'cat', 'bird'],
                ],
                'numberOfArticles' => [
                    'type' => 'integer',
                    'description' => 'The number of articles given to the tool',
                    'const' => 42,
                ],
                'infoEmail' => [
                    'type' => 'string',
                    'description' => 'The info email given to the tool',
                    'const' => 'info@example.de',
                ],
                'locales' => [
                    'type' => 'string',
                    'description' => 'The locales given to the tool',
                    'const' => ['de', 'en'],
                ],
                'text' => [
                    'type' => 'string',
                    'description' => 'The text given to the tool',
                    'pattern' => '^[a-zA-Z]+$',
                    'minLength' => 1,
                    'maxLength' => 10,
                ],
                'number' => [
                    'type' => 'integer',
                    'description' => 'The number given to the tool',
                    'minimum' => 1,
                    'maximum' => 10,
                    'multipleOf' => 2,
                    'exclusiveMinimum' => 1,
                    'exclusiveMaximum' => 10,
                ],
                'products' => [
                    'type' => 'array',
                    'description' => 'The products given to the tool',
                    'minItems' => 1,
                    'maxItems' => 10,
                    'uniqueItems' => true,
                    'minContains' => 1,
                    'maxContains' => 10,
                ],
                'shippingAddress' => [
                    'type' => 'object',
                    'description' => 'The shipping address given to the tool',
                    'required' => true,
                    'minProperties' => 1,
                    'maxProperties' => 10,
                    'dependentRequired' => true,
                ],
            ],
            'required' => [
                'animal',
                'numberOfArticles',
                'infoEmail',
                'locales',
                'text',
                'number',
                'products',
                'shippingAddress',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function detectParameterDefinitionOptional(): void
    {
        $actual = $this->analyzer->getDefinition(ToolOptionalParam::class, 'bar');
        $expected = [
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
            'required' => [
                'text',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function detectParameterDefinitionNone(): void
    {
        $actual = $this->analyzer->getDefinition(ToolNoParams::class, '__invoke');

        self::assertNull($actual);
    }

    #[Test]
    public function getParameterDescriptionWithoutDocBlock(): void
    {
        $targetMethod = self::createStub(\ReflectionMethod::class);
        $targetMethod->method('getDocComment')->willReturn(false);

        $methodToTest = new \ReflectionMethod(ParameterAnalyzer::class, 'getParameterDescription');

        self::assertSame(
            '',
            $methodToTest->invoke(
                $this->analyzer,
                $targetMethod,
                'myParam',
            )
        );
    }

    #[Test]
    #[DataProvider('provideGetParameterDescriptionCases')]
    public function getParameterDescriptionWithDocs(string $docComment, string $expectedResult): void
    {
        $targetMethod = self::createStub(\ReflectionMethod::class);
        $targetMethod->method('getDocComment')->willReturn($docComment);

        $methodToTest = new \ReflectionMethod(ParameterAnalyzer::class, 'getParameterDescription');

        self::assertSame(
            $expectedResult,
            $methodToTest->invoke(
                $this->analyzer,
                $targetMethod,
                'myParam',
            )
        );
    }

    public static function provideGetParameterDescriptionCases(): \Generator
    {
        yield 'empty doc block' => [
            'docComment' => '',
            'expectedResult' => '',
        ];

        yield 'single line doc block with description' => [
            'docComment' => '/** @param string $myParam The description */',
            'expectedResult' => 'The description',
        ];

        yield 'multi line doc block with description and other tags' => [
            'docComment' => <<<'TEXT'
                    /**
                     * @param string $myParam The description
                     * @return void
                     */
                TEXT,
            'expectedResult' => 'The description',
        ];

        yield 'multi line doc block with multiple parameters' => [
            'docComment' => <<<'TEXT'
                    /**
                     * @param string $myParam The description
                     * @param string $anotherParam The wrong description
                     */
                TEXT,
            'expectedResult' => 'The description',
        ];

        yield 'multi line doc block with parameter that is not searched for' => [
            'docComment' => <<<'TEXT'
                    /**
                     * @param string $unknownParam The description
                     */
                TEXT,
            'expectedResult' => '',
        ];
    }
}
