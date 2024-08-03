<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Exception\MessageBagNotFound;
use Psr\Cache\CacheItemPoolInterface;

final class MessageStore
{
    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    public function load(string $id): MessageBag
    {
        $item = $this->cache->getItem($id);

        if (!$item->isHit()) {
            throw MessageBagNotFound::notFound($id);
        }

        return $item->get();
    }

    public function save(MessageBag $messages, $id): void
    {
        $item = $this->cache->getItem($id);
        $item->set($messages);
        $this->cache->save($item);
    }
}
