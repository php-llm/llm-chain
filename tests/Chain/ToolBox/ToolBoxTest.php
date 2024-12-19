<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use PhpLlm\LlmChain\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningArray;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningFloat;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningInteger;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningJsonSerializable;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolReturningStringable;
use PHPUnit\Framework\Attributes\CoversClass;
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
    public function executeWithToolReturningString(): void
    {
        self::assertSame(
            'Hello says "3".',
            $this->toolBox->execute(
                new ToolCall('call_1234', 'tool_required_params', ['text' => 'Hello', 'number' => 3])
            )
        );
    }

    #[Test]
    public function executeWithToolReturningArray(): void
    {
        self::assertSame(
            '{"foo":"bar"}',
            $this->toolBox->execute(new ToolCall('call_1234', 'tool_returning_array'))
        );
    }

    #[Test]
    public function executeWithToolReturningJsonSerializable(): void
    {
        self::assertSame(
            '{"foo":"bar"}',
            $this->toolBox->execute(new ToolCall('call_1234', 'tool_returning_json_serializable'))
        );
    }

    #[Test]
    public function executeWithToolReturningInteger(): void
    {
        self::assertSame(
            '42',
            $this->toolBox->execute(new ToolCall('call_1234', 'tool_returning_integer'))
        );
    }

    #[Test]
    public function executeWithToolReturningFloat(): void
    {
        self::assertSame(
            '42.42',
            $this->toolBox->execute(new ToolCall('call_1234', 'tool_returning_float'))
        );
    }

    #[Test]
    public function executeWithToolReturningStringable(): void
    {
        self::assertSame(
            'Hi!',
            $this->toolBox->execute(new ToolCall('call_1234', 'tool_returning_stringable'))
        );
    }
}
