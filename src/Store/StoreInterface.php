<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Document\VectorDocument;

interface StoreInterface
{
    public function add(VectorDocument ...$documents): void;
}
