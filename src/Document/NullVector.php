<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use PhpLlm\LlmChain\Exception\RuntimeException;

final class NullVector implements VectorInterface
{
    public function getData(): array
    {
        throw new RuntimeException('getData() method cannot be called on a NullVector.');
    }

    public function getDimensions(): int
    {
        throw new RuntimeException('getDimensions() method cannot be called on a NullVector.');
    }
