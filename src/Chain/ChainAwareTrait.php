<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

trait ChainAwareTrait
{
    private ChainInterface $chain;

    public function setChain(ChainInterface $chain): void
    {
        $this->chain = $chain;
    }
}
