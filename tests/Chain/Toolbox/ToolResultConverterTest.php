<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\ToolResultConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolResultConverter::class)]
final class ToolResultConverterTest extends TestCase
{
    #[Test]
    #[DataProvider('provideResults')]
    public function testConvert(mixed $result, ?string $expected): void
    {
        $converter = new ToolResultConverter();

        self::assertSame($expected, $converter->convert($result));
    }

    public static function provideResults(): \Generator
    {
        yield 'null' => [null, null];

        yield 'integer' => [42, '42'];

        yield 'float' => [42.42, '42.42'];

        yield 'array' => [['key' => 'value'], '{"key":"value"}'];

        yield 'string' => ['plain string', 'plain string'];

        yield 'stringable' => [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'stringable';
                }
            },
            'stringable',
        ];

        yield 'json_serializable' => [
            new class implements \JsonSerializable {
                public function jsonSerialize(): array
                {
                    return ['key' => 'value'];
                }
            },
            '{"key":"value"}',
        ];
    }
}
