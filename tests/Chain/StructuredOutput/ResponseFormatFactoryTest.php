<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\StructuredOutput;

use PhpLlm\LlmChain\Chain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\Chain\StructuredOutput\SchemaFactory;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\User;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\UserWithAtParamAnnotation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseFormatFactory::class)]
#[UsesClass(SchemaFactory::class)]
final class ResponseFormatFactoryTest extends TestCase
{
    #[Test]
    #[DataProvider('createProvider')]
    /**
     * @param array<mixed> $expected
     * @param class-string $class
     */
    public function create(array $expected, string $class): void
    {
        self::assertSame($expected, (new ResponseFormatFactory())->create($class));
    }

    public static function createProvider(): iterable
    {
        yield [
            [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'User',
                    'schema' => [
                        'title' => 'User',
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name of the user in lowercase',
                            ],
                            'createdAt' => [
                                'type' => 'string',
                                'format' => 'date-time',
                            ],
                            'isActive' => ['type' => 'boolean'],
                            'age' => ['type' => ['integer', 'null']],
                        ],
                        'required' => ['id', 'name', 'createdAt', 'isActive'],
                        'additionalProperties' => false,
                    ],
                    'strict' => true,
                ],
            ],
            User::class,
        ];

        yield [
            [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'User',
                    'schema' => [
                        'title' => 'User',
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name of the user in lowercase',
                            ],
                            'createdAt' => [
                                'type' => 'string',
                                'format' => 'date-time',
                            ],
                            'isActive' => ['type' => 'boolean'],
                            'age' => ['type' => ['integer', 'null']],
                        ],
                        'required' => ['id', 'name', 'createdAt', 'isActive'],
                        'additionalProperties' => false,
                    ],
                    'strict' => true,
                ],
            ],
            UserWithAtParamAnnotation::class,
        ];
    }
}
