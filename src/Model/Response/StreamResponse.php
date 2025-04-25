<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final class StreamResponse extends BaseResponse
{
    public function __construct(
        private readonly \Generator $generator,
    ) {
    }

    public function getContent(): \Generator
    {
        yield from $this->generator;
    }
}
