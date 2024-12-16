<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain;

interface ChainAwareProcessor
{
    public function setChain(Chain $chain): void;

    public function addOutputProcessor(OutputProcessor $outputProcessor): self;

    public function addInputProcessor(InputProcessor $outputProcessor): self;

    /**
     * @return array<InputProcessor>
     */
    public function getInputProcessors(): array;

    /**
     * @return array<OutputProcessor>
     */
    public function getOutputProcessors(): array;

    /**
     * @param array<InputProcessor> $inputProcessors
     */
    public function setInputProcessors(array $inputProcessors): self;

    /**
     * @param array<OutputProcessor> $outputProcessors
     */
    public function setOutputProcessors(array $outputProcessors): self;
}
