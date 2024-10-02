<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Store\StoreInterface;

final class TestStore implements StoreInterface
{
    /**
     * @var Document[]
     */
    public array $documents = [];

    public int $addDocumentsCalls = 0;
    public int $addDocumentCalls = 0;

    public function addDocuments(array $documents): void
    {
        ++$this->addDocumentsCalls;
        $this->documents = array_merge($this->documents, $documents);
    }

    public function addDocument(Document $document): void
    {
        ++$this->addDocumentCalls;
        $this->documents[] = $document;
    }
}
