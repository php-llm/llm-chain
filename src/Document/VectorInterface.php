<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

interface VectorInterface
{
    /**
     * @return list<float>
     */
    public function getData(): array;

    public function getDimensions(): int;
}
