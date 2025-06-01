<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Store\Document\VectorDocument;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface StoreInterface
{
    public function add(VectorDocument ...$documents): void;
}
