<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\ToolBox\Attribute;

use PhpLlm\LlmChain\ToolBox\Attribute\ToolParameter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ToolParameter::class)]
final class ToolParameterTest extends TestCase
{
    public function testValidEnum(): void
    {
        $enum = ['value1', 'value2'];
        $toolParameter = new ToolParameter(enum: $enum);
        $this->assertSame($enum, $toolParameter->enum);
    }

    public function testInvalidEnumContainsNonString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $enum = ['value1', 2];
        new ToolParameter(enum: $enum);
    }

    public function testValidConstString(): void
    {
        $const = 'constant value';
        $toolParameter = new ToolParameter(const: $const);
        $this->assertSame($const, $toolParameter->const);
    }

    public function testInvalidConstEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $const = '   ';
        new ToolParameter(const: $const);
    }

    public function testValidPattern(): void
    {
        $pattern = '/^[a-z]+$/';
        $toolParameter = new ToolParameter(pattern: $pattern);
        $this->assertSame($pattern, $toolParameter->pattern);
    }

    public function testInvalidPatternEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $pattern = '   ';
        new ToolParameter(pattern: $pattern);
    }

    public function testValidMinLength(): void
    {
        $minLength = 5;
        $toolParameter = new ToolParameter(minLength: $minLength);
        $this->assertSame($minLength, $toolParameter->minLength);
    }

    public function testInvalidMinLengthNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minLength: -1);
    }

    public function testValidMinLengthAndMaxLength(): void
    {
        $minLength = 5;
        $maxLength = 10;
        $toolParameter = new ToolParameter(minLength: $minLength, maxLength: $maxLength);
        $this->assertSame($minLength, $toolParameter->minLength);
        $this->assertSame($maxLength, $toolParameter->maxLength);
    }

    public function testInvalidMaxLengthLessThanMinLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minLength: 10, maxLength: 5);
    }

    public function testValidMinimum(): void
    {
        $minimum = 0;
        $toolParameter = new ToolParameter(minimum: $minimum);
        $this->assertSame($minimum, $toolParameter->minimum);
    }

    public function testInvalidMinimumNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minimum: -1);
    }

    public function testValidMultipleOf(): void
    {
        $multipleOf = 5;
        $toolParameter = new ToolParameter(multipleOf: $multipleOf);
        $this->assertSame($multipleOf, $toolParameter->multipleOf);
    }

    public function testInvalidMultipleOfNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(multipleOf: -5);
    }

    public function testValidExclusiveMinimumAndMaximum(): void
    {
        $exclusiveMinimum = 1;
        $exclusiveMaximum = 10;
        $toolParameter = new ToolParameter(exclusiveMinimum: $exclusiveMinimum, exclusiveMaximum: $exclusiveMaximum);
        $this->assertSame($exclusiveMinimum, $toolParameter->exclusiveMinimum);
        $this->assertSame($exclusiveMaximum, $toolParameter->exclusiveMaximum);
    }

    public function testInvalidExclusiveMaximumLessThanExclusiveMinimum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(exclusiveMinimum: 10, exclusiveMaximum: 5);
    }

    public function testValidMinItemsAndMaxItems(): void
    {
        $minItems = 1;
        $maxItems = 5;
        $toolParameter = new ToolParameter(minItems: $minItems, maxItems: $maxItems);
        $this->assertSame($minItems, $toolParameter->minItems);
        $this->assertSame($maxItems, $toolParameter->maxItems);
    }

    public function testInvalidMaxItemsLessThanMinItems(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minItems: 5, maxItems: 1);
    }

    public function testValidUniqueItemsTrue(): void
    {
        $toolParameter = new ToolParameter(uniqueItems: true);
        $this->assertTrue($toolParameter->uniqueItems);
    }

    public function testInvalidUniqueItemsFalse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(uniqueItems: false);
    }

    public function testValidMinContainsAndMaxContains(): void
    {
        $minContains = 1;
        $maxContains = 3;
        $toolParameter = new ToolParameter(minContains: $minContains, maxContains: $maxContains);
        $this->assertSame($minContains, $toolParameter->minContains);
        $this->assertSame($maxContains, $toolParameter->maxContains);
    }

    public function testInvalidMaxContainsLessThanMinContains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minContains: 3, maxContains: 1);
    }

    public function testValidRequired(): void
    {
        $toolParameter = new ToolParameter(required: true);
        $this->assertTrue($toolParameter->required);
    }

    public function testValidMinPropertiesAndMaxProperties(): void
    {
        $minProperties = 1;
        $maxProperties = 5;
        $toolParameter = new ToolParameter(minProperties: $minProperties, maxProperties: $maxProperties);
        $this->assertSame($minProperties, $toolParameter->minProperties);
        $this->assertSame($maxProperties, $toolParameter->maxProperties);
    }

    public function testInvalidMaxPropertiesLessThanMinProperties(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minProperties: 5, maxProperties: 1);
    }

    public function testValidDependentRequired(): void
    {
        $toolParameter = new ToolParameter(dependentRequired: true);
        $this->assertTrue($toolParameter->dependentRequired);
    }

    public function testValidCombination(): void
    {
        $toolParameter = new ToolParameter(
            enum: ['value1', 'value2'],
            const: 'constant',
            pattern: '/^[a-z]+$/',
            minLength: 5,
            maxLength: 10,
            minimum: 0,
            maximum: 100,
            multipleOf: 5,
            exclusiveMinimum: 1,
            exclusiveMaximum: 99,
            minItems: 1,
            maxItems: 10,
            uniqueItems: true,
            minContains: 1,
            maxContains: 5,
            required: true,
            minProperties: 1,
            maxProperties: 5,
            dependentRequired: true
        );

        $this->assertInstanceOf(ToolParameter::class, $toolParameter);
    }

    public function testInvalidCombination(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minLength: -1, maxLength: -2);
    }
}
