<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Pipelines\Pipeline;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class PipelineExecution
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $result = null;

    /**
     * @param array<mixed>|string|object $input
     */
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly object|array|string $input,
    ) {
    }

    public function getPipeline(): Pipeline
    {
        return $this->pipeline;
    }

    /**
     * @return array<mixed>
     */
    public function getResult(): array
    {
        if (null === $this->result) {
            $this->result = ($this->pipeline)($this->input);
        }

        return $this->result;
    }
}
