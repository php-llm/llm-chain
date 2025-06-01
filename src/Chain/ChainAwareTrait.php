<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
trait ChainAwareTrait
{
    private ChainInterface $chain;

    public function setChain(ChainInterface $chain): void
    {
        $this->chain = $chain;
    }
}
