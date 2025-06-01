<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Vector;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
interface VectorInterface
{
    /**
     * @return list<float>
     */
    public function getData(): array;

    public function getDimensions(): int;
}
