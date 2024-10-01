<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\ToolBox\Attribute;

use PhpLlm\LlmChain\Tests\Fixture\FaqItem;
use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsTool::class)]
final class AsToolTest extends TestCase
{
    #[Test]
    public function canBeConstructed(): void
    {
        $attribute = new AsTool(
            name: 'name',
            description: 'description',
        );

        self::assertSame('name', $attribute->name);
        self::assertSame('description', $attribute->description);
        self::assertNull($attribute->responseFormat);
    }

    public function testValidResponseFormatClassString(): void
    {
        $attribute = new AsTool(
            name: 'name',
            description: 'description',
            responseFormat: FaqItem::class,
        );

        self::assertSame('name', $attribute->name);
        self::assertSame('description', $attribute->description);
        self::assertSame(FaqItem::class, $attribute->responseFormat);
    }

    public function testValidResponseFormatArray(): void
    {
        $responseFormat = [
            'type' => 'object',
            'properties' => [
                'message' => [
                    'type' => 'string',
                ],
            ],
            'additionalProperties' => false,
            'required' => ['message'],
        ];

        $attribute = new AsTool(
            name: 'name',
            description: 'description',
            responseFormat: $responseFormat
        );

        self::assertSame('name', $attribute->name);
        self::assertSame('description', $attribute->description);
        self::assertSame($responseFormat, $attribute->responseFormat);
    }

    public function testInvalidResponseFormatClassString(): void
    {
        /** @var class-string $nonExistantClassString */
        $nonExistantClassString = 'NonExistentClass';

        $this->expectException(\InvalidArgumentException::class);
        new AsTool(
            name: 'name',
            description: 'description',
            responseFormat: $nonExistantClassString
        );
    }

    public function testInvalidResponseFormatEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AsTool(
            name: 'name',
            description: 'description',
            responseFormat: []
        );
    }
}
