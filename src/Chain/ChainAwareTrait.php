<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBoxInterface;

trait ChainAwareTrait
{
    private Chain $chain;

    private ToolBoxInterface $toolBox;

    /**
     * @var InputProcessor[]
     */
    private array $inputProcessors;

    /**
     * @var OutputProcessor[]
     */
    private array $outputProcessors;

    public function setChain(Chain $chain): void
    {
        $this->chain = $chain;
    }

    public function getInputProcessors(): array
    {
        return $this->inputProcessors;
    }

    public function getOutputProcessors(): array
    {
        return $this->outputProcessors;
    }

    public function addOutputProcessor(OutputProcessor $outputProcessor): self
    {
        if (!in_array($outputProcessor, $this->outputProcessors)) {
            $this->outputProcessors[] = $outputProcessor;
        }

        return $this;
    }

    public function addInputProcessor(InputProcessor $outputProcessor): self
    {
        if (!in_array($outputProcessor, $this->inputProcessors)) {
            $this->inputProcessors[] = $outputProcessor;
        }

        return $this;
    }

    public function setOutputProcessors(array $outputProcessors): self
    {
        $this->outputProcessors = $outputProcessors;

        return $this;
    }

    public function setInputProcessors(array $inputProcessors): self
    {
        $this->inputProcessors = $inputProcessors;

        return $this;
    }

    public function withToolBox(ToolBoxInterface $toolBox): self
    {
        $chain = clone $this;
        $chain->toolBox = $toolBox;

        return $chain;
    }
}
