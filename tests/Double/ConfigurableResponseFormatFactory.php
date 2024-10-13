<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Chain\StructuredOutput\ResponseFormatFactoryInterface;

final readonly class ConfigurableResponseFormatFactory implements ResponseFormatFactoryInterface
{
    /**
     * @param array<mixed> $responseFormat
     */
    public function __construct(
        private array $responseFormat = [],
    ) {
    }

    public function create(string $responseClass): array
    {
        return $this->responseFormat;
    }
}
