<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

interface InitializableStoreInterface extends StoreInterface
{
    /**
     * @param array<mixed> $options
     */
    public function initialize(array $options = []): void;
}
