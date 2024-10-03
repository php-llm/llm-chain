<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Response;

final readonly class StreamResponse implements ResponseInterface
{
    public function __construct(
        private \Generator $generator,
    ) {
    }

    public function getChoices(): array
    {
        throw new \LogicException('Stream response does not have choices');
    }

    public function getContent(): \Generator
    {
        yield from $this->generator;
    }

    public function getToolCalls(): array
    {
        return [];
    }

    public function hasToolCalls(): bool
    {
        return false;
    }
}
