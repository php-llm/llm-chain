<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Chat\MessageStore;

use PhpLlm\LlmChain\Chain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheStore implements MessageStoreInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private string $cacheKey,
        private int $ttl = 86400,
    ) {
        if (!interface_exists(CacheItemPoolInterface::class)) {
            throw new \RuntimeException('For using the CacheStore as message store, a PSR-6 cache implementation is required. Try running "composer require symfony/cache" or another PSR-6 compatible cache.');
        }
    }

    public function save(MessageBagInterface $messages): void
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
