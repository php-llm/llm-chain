<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Memory;

use PhpLlm\LlmChain\Chain\Input;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
interface MemoryProviderInterface
{
    public function loadMemory(Input $input): ?Memory;
}
