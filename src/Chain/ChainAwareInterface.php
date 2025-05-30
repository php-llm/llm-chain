<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

interface ChainAwareInterface
{
    public function setChain(Chain $chain): void;
}
