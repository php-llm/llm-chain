<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Store\StoreInterface;

final class SpyStore implements StoreInterface
{
    public function addDocuments(array $documents): void
    {
        $this->documents = array_merge($this->documents, $documents);
    }

    public function addDocument(Document $document): void
    {
        $this->documents[] = $document;
    }

    /**
     * @var Document[]
     */
    public array $documents = [];
}
