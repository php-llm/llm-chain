<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Pipelines\Pipeline;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class PipelineResponse implements ResponseInterface
{
    /**
     * @var array<mixed>
     */
    private array $result;

    /**
     * @param object|array<mixed>|string $input
     */
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly object|array|string $input,
    ) {
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function getHeaders(bool $throw = true): array
    {
        return [];
    }

    public function getContent(bool $throw = true): string
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @return array<mixed>
     */
    public function toArray(bool $throw = true): array
    {
        return $this->result ?? $this->result = ($this->pipeline)($this->input);
    }

    public function cancel(): void
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getInfo(?string $type = null): mixed
    {
        throw new \RuntimeException('Not implemented');
    }
}
