<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\StructuredOutput;

use PhpLlm\LlmChain\Chain\JsonSchema\Factory;

use function Symfony\Component\String\u;

final readonly class ResponseFormatFactory implements ResponseFormatFactoryInterface
{
    public function __construct(
        private Factory $schemaFactory = new Factory(),
    ) {
    }

    public function create(string $responseClass): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => u($responseClass)->afterLast('\\')->toString(),
                'schema' => $this->schemaFactory->buildProperties($responseClass),
                'strict' => true,
            ],
        ];
    }
}
