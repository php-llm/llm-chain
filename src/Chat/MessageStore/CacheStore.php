<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chat\MessageStore;

use PhpLlm\LlmChain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheStore implements MessageStoreInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private string $cacheKey,
        private int $ttl = 86400,
    ) {
    }

    public function save(MessageBag $messages): void
    {
        $item = $this->cache->getItem($this->cacheKey);

        $item->set($messages);
        $item->expiresAfter($this->ttl);

        $this->cache->save($item);
    }

    public function load(): MessageBag
    {
        $item = $this->cache->getItem($this->cacheKey);

        return $item->isHit() ? $item->get() : new MessageBag();
    }

    public function clear(): void
    {
        $this->cache->deleteItem($this->cacheKey);
    }
}
