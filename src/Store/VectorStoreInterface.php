<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\Document\VectorDocument;

interface VectorStoreInterface extends StoreInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @return VectorDocument[]
     */
    public function query(Vector $vector, array $options = [], ?float $minScore = null): array;
}
