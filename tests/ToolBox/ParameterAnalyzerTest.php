<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\ToolBox;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;

#[CoversClass(ParameterAnalyzer::class)]
final class ParameterAnalyzerTest extends TestCase
{
    private ParameterAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new ParameterAnalyzer();
    }

    public function testDetectParameterDefinitionRequired(): void
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
                    'type' => 'int',
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

    public function testDetectParameterDefinitionOptional(): void
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
                    'type' => 'int',
                    'description' => 'A number given to the tool',
                ],
            ],
            'required' => [
                'text',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    public function testDetectParameterDefinitionNone(): void
    {
        $actual = $this->analyzer->getDefinition(ToolNoParams::class, '__invoke');

        self::assertNull($actual);
    }
}
