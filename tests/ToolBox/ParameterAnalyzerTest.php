<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Tests\ToolBox;

use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolOptionalParam;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\ToolBox\AsTool;
use PhpLlm\LlmChain\ToolBox\Metadata;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParameterAnalyzer::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ParameterAnalyzer::class)]
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
}
