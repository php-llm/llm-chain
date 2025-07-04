<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\TransformersPHP;

use PhpLlm\LlmChain\Platform\Response\RawResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class RawPipelineResponse implements RawResponseInterface
{
    public function __construct(
        private PipelineExecution $pipelineExecution,
    ) {
    }

    public function getRawData(): array
    {
        return $this->pipelineExecution->getResult();
    }

    public function getRawObject(): PipelineExecution
    {
        return $this->pipelineExecution;
    }
}
