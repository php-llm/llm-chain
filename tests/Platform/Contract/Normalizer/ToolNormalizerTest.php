<?php

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\ToolNormalizer;
use PhpLlm\LlmChain\Platform\Tool\ExecutionReference;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolException;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolNormalizer::class)]
#[Small]
class ToolNormalizerTest extends TestCase
{
    #[Test]
    #[DataProvider('provideTools')]
    public function normalize(Tool $tool, array $expected): void
    {
        self::assertSame($expected, (new ToolNormalizer())->normalize($tool));
    }

    public static function provideTools(): \Generator
    {
        yield 'required params' => [
            new Tool(
                new ExecutionReference(ToolRequiredParams::class, 'bar'),
                'tool_required_params',
                'A tool with required parameters',
                [
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
            ),
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
        ];

        yield 'optional param' => [
            new Tool(
                new ExecutionReference(ToolOptionalParam::class, 'bar'),
                'tool_optional_param',
                'A tool with one optional parameter',
                [
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
                ],
            ),
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
                        'required' => ['text'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        yield 'no params' => [
            new Tool(
                new ExecutionReference(ToolNoParams::class),
                'tool_no_params',
                'A tool without parameters',
            ),
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_no_params',
                    'description' => 'A tool without parameters',
                ],
            ],
        ];

        yield 'exception' => [
            new Tool(
                new ExecutionReference(ToolException::class, 'bar'),
                'tool_exception',
                'This tool is broken',
            ),
            [
                'type' => 'function',
                'function' => [
                    'name' => 'tool_exception',
                    'description' => 'This tool is broken',
                ],
            ],
        ];
    }
}
