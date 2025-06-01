<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface OutputProcessorInterface
{
    public function processOutput(Output $output): void;
}
