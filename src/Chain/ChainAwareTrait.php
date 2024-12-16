<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain\ToolBox\ToolBoxInterface;
use PhpLlm\LlmChain\ChainInterface;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;

trait ChainAwareTrait
{
    private ChainInterface $chain;

    private ToolBoxInterface $toolBox;

    /**
     * @var InputProcessor[]
     */
    private array $inputProcessors = [];

    /**
     * @var OutputProcessor[]
     */
    private array $outputProcessors = [];

    public function setChain(ChainInterface $chain): void
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

    public function setOutputProcessors(iterable $outputProcessors): self
    {
        foreach ($outputProcessors as $processor) {
            if (!$processor instanceof OutputProcessor) {
                throw new InvalidArgumentException(sprintf('Processor %s must implement %s interface.', $processor::class, OutputProcessor::class));
            }

            $this->addOutputProcessor($processor);
        }

        return $this;
    }

    public function setInputProcessors(iterable $inputProcessors): self
    {
        foreach ($inputProcessors as $processor) {
            if (!$processor instanceof InputProcessor) {
                throw new InvalidArgumentException(sprintf('Processor %s must implement %s interface.', $processor::class, InputProcessor::class));
            }

            $this->addInputProcessor($processor);
        }

        return $this;
    }

    public function withToolBox(ToolBoxInterface $toolBox): self
    {
        $chain = clone $this;
        $chain->toolBox = $toolBox;

        return $chain;
    }
}
