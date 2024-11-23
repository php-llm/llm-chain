<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\ToolBox\Attribute;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\ToolParameter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ToolParameter::class)]
final class ToolParameterTest extends TestCase
{
    #[Test]
    public function validEnum(): void
    {
        $enum = ['value1', 'value2'];
        $toolParameter = new ToolParameter(enum: $enum);
        self::assertSame($enum, $toolParameter->enum);
    }

    #[Test]
    public function invalidEnumContainsNonString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $enum = ['value1', 2];
        new ToolParameter(enum: $enum);
    }

    #[Test]
    public function validConstString(): void
    {
        $const = 'constant value';
        $toolParameter = new ToolParameter(const: $const);
        self::assertSame($const, $toolParameter->const);
    }

    #[Test]
    public function invalidConstEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $const = '   ';
        new ToolParameter(const: $const);
    }

    #[Test]
    public function validPattern(): void
    {
        $pattern = '/^[a-z]+$/';
        $toolParameter = new ToolParameter(pattern: $pattern);
        self::assertSame($pattern, $toolParameter->pattern);
    }

    #[Test]
    public function invalidPatternEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $pattern = '   ';
        new ToolParameter(pattern: $pattern);
    }

    #[Test]
    public function validMinLength(): void
    {
        $minLength = 5;
        $toolParameter = new ToolParameter(minLength: $minLength);
        self::assertSame($minLength, $toolParameter->minLength);
    }

    #[Test]
    public function invalidMinLengthNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minLength: -1);
    }

    #[Test]
    public function validMinLengthAndMaxLength(): void
    {
        $minLength = 5;
        $maxLength = 10;
        $toolParameter = new ToolParameter(minLength: $minLength, maxLength: $maxLength);
        self::assertSame($minLength, $toolParameter->minLength);
        self::assertSame($maxLength, $toolParameter->maxLength);
    }

    #[Test]
    public function invalidMaxLengthLessThanMinLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minLength: 10, maxLength: 5);
    }

    #[Test]
    public function validMinimum(): void
    {
        $minimum = 0;
        $toolParameter = new ToolParameter(minimum: $minimum);
        self::assertSame($minimum, $toolParameter->minimum);
    }

    #[Test]
    public function invalidMinimumNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minimum: -1);
    }

    #[Test]
    public function validMultipleOf(): void
    {
        $multipleOf = 5;
        $toolParameter = new ToolParameter(multipleOf: $multipleOf);
        self::assertSame($multipleOf, $toolParameter->multipleOf);
    }

    #[Test]
    public function invalidMultipleOfNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(multipleOf: -5);
    }

    #[Test]
    public function validExclusiveMinimumAndMaximum(): void
    {
        $exclusiveMinimum = 1;
        $exclusiveMaximum = 10;
        $toolParameter = new ToolParameter(exclusiveMinimum: $exclusiveMinimum, exclusiveMaximum: $exclusiveMaximum);
        self::assertSame($exclusiveMinimum, $toolParameter->exclusiveMinimum);
        self::assertSame($exclusiveMaximum, $toolParameter->exclusiveMaximum);
    }

    #[Test]
    public function invalidExclusiveMaximumLessThanExclusiveMinimum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(exclusiveMinimum: 10, exclusiveMaximum: 5);
    }

    #[Test]
    public function validMinItemsAndMaxItems(): void
    {
        $minItems = 1;
        $maxItems = 5;
        $toolParameter = new ToolParameter(minItems: $minItems, maxItems: $maxItems);
        self::assertSame($minItems, $toolParameter->minItems);
        self::assertSame($maxItems, $toolParameter->maxItems);
    }

    #[Test]
    public function invalidMaxItemsLessThanMinItems(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minItems: 5, maxItems: 1);
    }

    #[Test]
    public function validUniqueItemsTrue(): void
    {
        $toolParameter = new ToolParameter(uniqueItems: true);
        self::assertTrue($toolParameter->uniqueItems);
    }

    #[Test]
    public function invalidUniqueItemsFalse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(uniqueItems: false);
    }

    #[Test]
    public function validMinContainsAndMaxContains(): void
    {
        $minContains = 1;
        $maxContains = 3;
        $toolParameter = new ToolParameter(minContains: $minContains, maxContains: $maxContains);
        self::assertSame($minContains, $toolParameter->minContains);
        self::assertSame($maxContains, $toolParameter->maxContains);
    }

    #[Test]
    public function invalidMaxContainsLessThanMinContains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minContains: 3, maxContains: 1);
    }

    #[Test]
    public function validRequired(): void
    {
        $toolParameter = new ToolParameter(required: true);
        self::assertTrue($toolParameter->required);
    }

    #[Test]
    public function validMinPropertiesAndMaxProperties(): void
    {
        $minProperties = 1;
        $maxProperties = 5;
        $toolParameter = new ToolParameter(minProperties: $minProperties, maxProperties: $maxProperties);
        self::assertSame($minProperties, $toolParameter->minProperties);
        self::assertSame($maxProperties, $toolParameter->maxProperties);
    }

    #[Test]
    public function invalidMaxPropertiesLessThanMinProperties(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minProperties: 5, maxProperties: 1);
    }

    #[Test]
    public function validDependentRequired(): void
    {
        $toolParameter = new ToolParameter(dependentRequired: true);
        self::assertTrue($toolParameter->dependentRequired);
    }

    #[Test]
    public function validCombination(): void
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

        self::assertInstanceOf(ToolParameter::class, $toolParameter);
    }

    #[Test]
    public function invalidCombination(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ToolParameter(minLength: -1, maxLength: -2);
    }
}
