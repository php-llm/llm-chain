<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Vector;

interface InitializableStoreInterface extends StoreInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function initialize(array $options = []): void;
}
