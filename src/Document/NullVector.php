<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

final class NullVector implements VectorInterface
{
    public function getData(): array
    {
        return [];
    }

    public function getDimensions(): int
    {
        return 0;
    }
}
