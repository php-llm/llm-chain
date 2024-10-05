<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain;

interface ChainAwareProcessor
{
    public function setChain(Chain $chain): void;
}
