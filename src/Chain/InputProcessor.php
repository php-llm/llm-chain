<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

interface InputProcessor
{
    public function processInput(Input $input): void;
}
