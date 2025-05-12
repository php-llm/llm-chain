<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\StoreInterface;

final class TestStore implements StoreInterface
{
    /**
     * @var VectorDocument[]
     */
    public array $documents = [];

    public int $addCalls = 0;

    public function add(VectorDocument ...$documents): void
    {
        ++$this->addCalls;
        $this->documents = array_merge($this->documents, $documents);
    }
}
