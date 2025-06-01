<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

interface OutputProcessorInterface
{
    public function processOutput(Output $output): void;
}
