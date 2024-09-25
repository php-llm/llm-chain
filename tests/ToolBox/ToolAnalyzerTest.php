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

use PhpLlm\LlmChain\Exception\InvalidToolImplementation;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolMultiple;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolRequiredParams;
use PhpLlm\LlmChain\Tests\ToolBox\Tool\ToolWrong;
use PhpLlm\LlmChain\ToolBox\AsTool;
use PhpLlm\LlmChain\ToolBox\Metadata;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolAnalyzer::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ParameterAnalyzer::class)]
#[UsesClass(InvalidToolImplementation::class)]
final class ToolAnalyzerTest extends TestCase
{
    private ToolAnalyzer $toolAnalyzer;

    protected function setUp(): void
    {
        $this->toolAnalyzer = new ToolAnalyzer(new ParameterAnalyzer());
    }

    #[Test]
    public function withoutAttribute(): void
    {
        $this->expectException(InvalidToolImplementation::class);
        iterator_to_array($this->toolAnalyzer->getMetadata(ToolWrong::class));
    }

    #[Test]
    public function getDefinition(): void
    {
        /** @var Metadata[] $actual */
        $actual = iterator_to_array($this->toolAnalyzer->getMetadata(ToolRequiredParams::class));

        self::assertCount(1, $actual);
        self::assertSame(ToolRequiredParams::class, $actual[0]->className);
        self::assertSame('tool_required_params', $actual[0]->name);
        self::assertSame('A tool with required parameters', $actual[0]->description);
        self::assertSame('bar', $actual[0]->method);
        self::assertIsArray($actual[0]->parameters);
    }

    #[Test]
    public function getDefinitionWithMultiple(): void
    {
        $actual = iterator_to_array($this->toolAnalyzer->getMetadata(ToolMultiple::class));

        self::assertCount(2, $actual);

        self::assertSame(ToolMultiple::class, $actual[0]->className);
        self::assertSame('tool_hello_world', $actual[0]->name);
        self::assertSame('Function to say hello', $actual[0]->description);
        self::assertSame('hello', $actual[0]->method);
        self::assertIsArray($actual[0]->parameters);

        self::assertSame(ToolMultiple::class, $actual[1]->className);
        self::assertSame('tool_required_params', $actual[1]->name);
        self::assertSame('Function to say a number', $actual[1]->description);
        self::assertSame('bar', $actual[1]->method);
        self::assertIsArray($actual[1]->parameters);
    }
}
