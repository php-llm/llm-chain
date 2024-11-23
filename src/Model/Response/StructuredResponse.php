<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final readonly class StructuredResponse implements ResponseInterface
{
    /**
     * @param object|array<string, mixed> $structuredOutput
     */
    public function __construct(
        private object|array $structuredOutput,
    ) {
    }

    /**
     * @return object|array<string, mixed>
     */
    public function getContent(): object|array
    {
        return $this->structuredOutput;
    }
}
