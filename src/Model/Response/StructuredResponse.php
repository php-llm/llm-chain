<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final class StructuredResponse extends BaseResponse
{
    /**
     * @param object|array<string, mixed> $structuredOutput
     */
    public function __construct(
        private readonly object|array $structuredOutput,
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
