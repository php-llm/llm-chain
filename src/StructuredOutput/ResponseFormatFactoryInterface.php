<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\StructuredOutput;

interface ResponseFormatFactoryInterface
{
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
    public function create(string $responseClass): array;
}
