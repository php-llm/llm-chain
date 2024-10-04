<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

final class Vector implements VectorInterface
{
    /**
     * @param list<float> $data
     */
    public function __construct(
        private readonly array $data,
        private ?int $dimensions = null,
    ) {
        if (null !== $dimensions && $dimensions !== count($data)) {
            throw new InvalidArgumentException('Vector must have '.$dimensions.' dimensions');
        }

        if (0 === count($data)) {
            throw new InvalidArgumentException('Vector must have at least one dimension');
        }

        if (is_int($dimensions) && count($data) !== $dimensions) {
            throw new InvalidArgumentException('Vector must have '.$dimensions.' dimensions');
        }

        if (null === $this->dimensions) {
            $this->dimensions = count($data);
        }
    }

    /**
     * @return list<float>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getDimensions(): int
    {
        return $this->dimensions;
    }
}
