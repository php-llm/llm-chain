<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

final class Vector
{
    /**
     * @param list<float> $data
     */
    private function __construct(
        private readonly array $data,
        private readonly ?int $dimensions = null,
    ) {
        if (null !== $dimensions && $dimensions !== count($data)) {
            throw new \InvalidArgumentException('Vector must have ' . $dimensions . ' dimensions');
        }

        if (0 === count($data)) {
            throw new \InvalidArgumentException('Vector must have at least one dimension');
        }

        if (null === $this->dimensions) {
            $this->dimensions = count($data);
        }
    }

    public static function create1536(array $data): self
    {
        if (1536 !== count($data)) {
            throw new \InvalidArgumentException('Vector must have 1536 dimensions');
        }

        return new self($data, 1536);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDimensions(): int
    {
        return $this->dimensions;
    }
}
