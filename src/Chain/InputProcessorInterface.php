<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

interface InputProcessorInterface
{
    public function processInput(Input $input): void;
}
