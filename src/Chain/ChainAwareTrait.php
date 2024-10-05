<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain;

trait ChainAwareTrait
{
    private Chain $chain;

    public function setChain(Chain $chain): void
    {
        $this->chain = $chain;
    }
}
