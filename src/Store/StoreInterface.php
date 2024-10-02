<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Document\EmbeddedDocument;

interface StoreInterface
{
    public function addDocument(EmbeddedDocument $document): void;

    /**
     * @param EmbeddedDocument[] $documents
     */
    public function addDocuments(array $documents): void;
}
