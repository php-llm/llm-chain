<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Vector;

use PhpLlm\LlmChain\Store\Exception\RuntimeException;

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
}
