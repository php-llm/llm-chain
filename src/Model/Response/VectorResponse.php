<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Document\Vector;

final readonly class VectorResponse implements ResponseInterface
{
    public function __construct(
        private Vector $vector,
    ) {
    }

    public function getContent(): Vector
    {
        return $this->vector;
    }
}
