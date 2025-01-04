<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use PhpLlm\LlmChain\Exception\ToolBoxException;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolException;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMisconfigured;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningArray;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningFloat;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningInteger;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningJsonSerializable;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningStringable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolBox::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ParameterAnalyzer::class)]
#[UsesClass(ToolAnalyzer::class)]
final class ToolBoxTest extends TestCase
{
    private ToolBox $toolBox;

    protected function setUp(): void
    {
        $this->toolBox = new ToolBox(new ToolAnalyzer(), [
            new ToolRequiredParams(),
            new ToolOptionalParam(),
            new ToolNoParams(),
            new ToolReturningArray(),
            new ToolReturningJsonSerializable(),
            new ToolReturningInteger(),
            new ToolReturningFloat(),
            new ToolReturningStringable(),
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
                        'required' => [
                            'text',
                            'number',
                        ],
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
                        'required' => [
                            'text',
                        ],
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
                    'name' => 'tool_returning_array',
                    'description' => 'A tool returning an array',
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_returning_json_serializable',
                    'description' => 'A tool returning an object which implements \JsonSerializable',
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_returning_integer',
                    'description' => 'A tool returning an integer',
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_returning_float',
                    'description' => 'A tool returning a float',
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_returning_stringable',
                    'description' => 'A tool returning an object which implements \Stringable',
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
        self::expectException(ToolBoxException::class);
        self::expectExceptionMessage('Tool not found for call: foo_bar_baz');

        $this->toolBox->execute(new ToolCall('call_1234', 'foo_bar_baz'));
    }

    #[Test]
    public function executeWithMisconfiguredTool(): void
    {
        self::expectException(ToolBoxException::class);
        self::expectExceptionMessage('Method "foo" not found in tool "PhpLlm\LlmChain\Tests\Fixture\Tool\ToolMisconfigured".');

        $toolBox = new ToolBox(new ToolAnalyzer(), [new ToolMisconfigured()]);

        $toolBox->execute(new ToolCall('call_1234', 'tool_misconfigured'));
    }

    #[Test]
    public function executeWithException(): void
    {
        self::expectException(ToolBoxException::class);
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

        yield 'tool_returning_array' => [
            '{"foo":"bar"}',
            'tool_returning_array',
        ];

        yield 'tool_returning_json_serializable' => [
            '{"foo":"bar"}',
            'tool_returning_json_serializable',
        ];

        yield 'tool_returning_integer' => [
            '42',
            'tool_returning_integer',
        ];

        yield 'tool_returning_float' => [
            '42.42',
            'tool_returning_float',
        ];

        yield 'tool_returning_stringable' => [
            'Hi!',
            'tool_returning_stringable',
        ];
    }
}
