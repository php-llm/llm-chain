<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Vector;

interface VectorStoreInterface extends StoreInterface
{
    /**
     * @return list<Document>
     */
    public function query(Vector $vector): array;
}
