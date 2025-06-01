<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\JsonSchema;

use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Attribute\With;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\DescriptionParser;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\MathReasoning;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\Step;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\User;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolWithToolParameterAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
#[UsesClass(With::class)]
#[UsesClass(DescriptionParser::class)]
final class FactoryTest extends TestCase
{
    private Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    protected function tearDown(): void
    {
        unset($this->factory);
    }

    #[Test]
    public function buildParametersDefinitionRequired(): void
    {
        $actual = $this->factory->buildParameters(ToolRequiredParams::class, 'bar');
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
            'required' => ['text', 'number'],
            'additionalProperties' => false,
        ];

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function buildParametersDefinitionRequiredWithAdditionalToolParameterAttribute(): void
    {
        $actual = $this->factory->buildParameters(ToolWithToolParameterAttribute::class, '__invoke');
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
                    'items' => ['type' => 'string'],
                    'description' => 'The products given to the tool',
                    'minItems' => 1,
                    'maxItems' => 10,
                    'uniqueItems' => true,
                    'minContains' => 1,
                    'maxContains' => 10,
                ],
                'shippingAddress' => [
                    'type' => 'string',
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
            'additionalProperties' => false,
        ];

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function buildParametersDefinitionOptional(): void
    {
        $actual = $this->factory->buildParameters(ToolOptionalParam::class, 'bar');
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
            'required' => ['text'],
            'additionalProperties' => false,
        ];

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function buildParametersDefinitionNone(): void
    {
        $actual = $this->factory->buildParameters(ToolNoParams::class, '__invoke');

        self::assertNull($actual);
    }

    #[Test]
    public function buildPropertiesForUserClass(): void
    {
        $expected = [
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
                'age' => ['type' => ['integer', 'null']],
            ],
            'required' => ['id', 'name', 'createdAt', 'isActive'],
            'additionalProperties' => false,
        ];

        $actual = $this->factory->buildProperties(User::class);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function buildPropertiesForMathReasoningClass(): void
    {
        $expected = [
            'type' => 'object',
            'properties' => [
                'steps' => [
                    'type' => 'array',
                    'items' => [
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

        $actual = $this->factory->buildProperties(MathReasoning::class);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function buildPropertiesForStepClass(): void
    {
        $expected = [
            'type' => 'object',
            'properties' => [
                'explanation' => ['type' => 'string'],
                'output' => ['type' => 'string'],
            ],
            'required' => ['explanation', 'output'],
            'additionalProperties' => false,
        ];

        $actual = $this->factory->buildProperties(Step::class);

        self::assertSame($expected, $actual);
    }
}
