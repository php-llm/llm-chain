<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\StructuredOutput;

use PhpLlm\LlmChain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\StructuredOutput\SchemaFactory;
use PhpLlm\LlmChain\Tests\StructuredOutput\Data\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseFormatFactory::class)]
final class ResponseFormatFactoryTest extends TestCase
{
    private ResponseFormatFactory $responseFormatFactory;

    protected function setUp(): void
    {
        $this->responseFormatFactory = new ResponseFormatFactory(SchemaFactory::create());
    }

    #[Test]
    public function create(): void
    {
        self::assertSame([
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
                    ],
                    'required' => ['id', 'name', 'createdAt', 'isActive'],
                    'additionalProperties' => false,
                ],
                'strict' => true,
            ],
        ], $this->responseFormatFactory->create(User::class));
    }
}
