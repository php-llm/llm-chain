<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\JsonSchema\DescriptionParser;
use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory\ChainFactory;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory\MemoryFactory;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory\ReflectionFactory;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolException;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMisconfigured;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoAttribute1;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolBox::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ReflectionFactory::class)]
#[UsesClass(MemoryFactory::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(ToolConfigurationException::class)]
#[UsesClass(ToolNotFoundException::class)]
#[UsesClass(ToolExecutionException::class)]
final class ToolBoxTest extends TestCase
{
    private ToolBox $toolBox;

    protected function setUp(): void
    {
        $this->toolBox = new ToolBox(new ReflectionFactory(), [
            new ToolRequiredParams(),
            new ToolOptionalParam(),
            new ToolNoParams(),
            new ToolException(),
        ]);
    }

    #[Test]
    public function toolsMap(): void
    {
        $actual = $this->toolBox->getMap();
        $expected = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_required_params',
                    'description' => 'A tool with required parameters',
                    'parameters' => [
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
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_optional_param',
                    'description' => 'A tool with one optional parameter',
                    'parameters' => [
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
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_no_params',
                    'description' => 'A tool without parameters',
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_exception',
                    'description' => 'This tool is broken',
                ],
            ],
        ];

        self::assertSame(json_encode($expected), json_encode($actual));
    }

    #[Test]
    public function executeWithUnknownTool(): void
    {
        self::expectException(ToolNotFoundException::class);
        self::expectExceptionMessage('Tool not found for call: foo_bar_baz');

        $this->toolBox->execute(new ToolCall('call_1234', 'foo_bar_baz'));
    }

    #[Test]
    public function executeWithMisconfiguredTool(): void
    {
        self::expectException(ToolConfigurationException::class);
        self::expectExceptionMessage('Method "foo" not found in tool "PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMisconfigured".');

        $toolBox = new ToolBox(new ReflectionFactory(), [new ToolMisconfigured()]);

        $toolBox->execute(new ToolCall('call_1234', 'tool_misconfigured'));
    }

    #[Test]
    public function executeWithException(): void
    {
        self::expectException(ToolExecutionException::class);
        self::expectExceptionMessage('Execution of tool "tool_exception" failed with error: Tool error.');

        $this->toolBox->execute(new ToolCall('call_1234', 'tool_exception'));
    }

    #[Test]
    #[DataProvider('executeProvider')]
    public function execute(string $expected, string $toolName, array $toolPayload = []): void
    {
        self::assertSame(
            $expected,
            $this->toolBox->execute(new ToolCall('call_1234', $toolName, $toolPayload)),
        );
    }

    /**
     * @return iterable<array{0: non-empty-string, 1: non-empty-string, 2?: array}>
     */
    public static function executeProvider(): iterable
    {
        yield 'tool_required_params' => [
            'Hello says "3".',
            'tool_required_params',
            ['text' => 'Hello', 'number' => 3],
        ];
    }

    #[Test]
    public function toolBoxMapWithMemoryFactory(): void
    {
        $memoryFactory = (new MemoryFactory())
            ->addTool(ToolNoAttribute1::class, 'happy_birthday', 'Generates birthday message');

        $toolBox = new ToolBox($memoryFactory, [new ToolNoAttribute1()]);
        $expected = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'happy_birthday',
                    'description' => 'Generates birthday message',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'the name of the person',
                            ],
                            'years' => [
                                'type' => 'integer',
                                'description' => 'the age of the person',
                            ],
                        ],
                        'required' => ['name', 'years'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        self::assertSame(json_encode($expected), json_encode($toolBox->getMap()));
    }

    #[Test]
    public function toolBoxExecutionWithMemoryFactory(): void
    {
        $memoryFactory = (new MemoryFactory())
            ->addTool(ToolNoAttribute1::class, 'happy_birthday', 'Generates birthday message');

        $toolBox = new ToolBox($memoryFactory, [new ToolNoAttribute1()]);
        $response = $toolBox->execute(new ToolCall('call_1234', 'happy_birthday', ['name' => 'John', 'years' => 30]));

        self::assertSame('Happy Birthday, John! You are 30 years old.', $response);
    }

    #[Test]
    public function toolBoxMapWithOverrideViaChain(): void
    {
        $factory1 = (new MemoryFactory())
            ->addTool(ToolOptionalParam::class, 'optional_param', 'Tool with optional param', 'bar');
        $factory2 = new ReflectionFactory();

        $toolBox = new ToolBox(new ChainFactory([$factory1, $factory2]), [new ToolOptionalParam()]);

        $expected = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'optional_param',
                    'description' => 'Tool with optional param',
                    'parameters' => [
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
                ],
            ],
        ];

        self::assertSame(json_encode($expected), json_encode($toolBox->getMap()));
    }
}
