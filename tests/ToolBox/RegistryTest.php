<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\Tests\ToolBox;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SymfonyLlm\LlmChain\Tests\ToolBox\Tool\ToolNoParams;
use SymfonyLlm\LlmChain\Tests\ToolBox\Tool\ToolOptionalParam;
use SymfonyLlm\LlmChain\Tests\ToolBox\Tool\ToolRequiredParams;
use SymfonyLlm\LlmChain\ToolBox\ParameterAnalyzer;
use SymfonyLlm\LlmChain\ToolBox\Registry;
use SymfonyLlm\LlmChain\ToolBox\ToolAnalyzer;

#[CoversClass(Registry::class)]
final class RegistryTest extends TestCase
{
    private Registry $registry;

    protected function setUp(): void
    {
        $toolAnalyzer = new ToolAnalyzer(new ParameterAnalyzer());
        $this->registry = new Registry($toolAnalyzer, [
            new ToolRequiredParams(),
            new ToolOptionalParam(),
            new ToolNoParams(),
        ]);
    }

    public function testFunctionsMap(): void
    {
        $actual = $this->registry->getMap();
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
                                'type' => 'int',
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
                                'type' => 'int',
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
        ];

        self::assertSame($expected, $actual);
    }

    public function testExecute(): void
    {
        $actual = $this->registry->execute('tool_required_params', '{"text":"Hello", "number":3}');
        $expected = 'Hello says "3".';

        self::assertSame($expected, $actual);
    }
}
