<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response\Metadata;

/**
 * @implements \IteratorAggregate<string, mixed>
 * @implements \ArrayAccess<string, mixed>
 */
class Metadata implements \JsonSerializable, \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    private array $metadata = [];

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->set($metadata);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function set(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function add(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->metadata);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function remove(string $key): void
    {
        unset($this->metadata[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }

    public function count(): int
    {
        return \count($this->metadata);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->metadata);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->add((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove((string) $offset);
    }
}
