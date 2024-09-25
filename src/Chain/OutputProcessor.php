<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

interface OutputProcessor
{
    public function processOutput(Output $output): mixed;
}
