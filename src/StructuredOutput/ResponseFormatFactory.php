<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\StructuredOutput;

use function Symfony\Component\String\u;

final readonly class ResponseFormatFactory
{
    public function __construct(
        private SchemaFactory $schemaFactory,
    ) {
    }

    /**
     * @param class-string $responseClass
     *
     * @return array{
     *     type: 'json_schema',
     *     json_schema: array{
     *         name: string,
     *         schema: array<string, mixed>,
     *         strict: true,
     *     }
     * }
     */
    public function create(string $responseClass): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => u($responseClass)->afterLast('\\')->toString(),
                'schema' => $this->schemaFactory->buildSchema($responseClass),
                'strict' => true,
            ],
        ];
    }
}
