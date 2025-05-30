<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Vector\Vector;

final class VectorResponse extends BaseResponse
{
    /**
     * @var Vector[]
     */
    private readonly array $vectors;

    public function __construct(Vector ...$vector)
    {
        $this->vectors = $vector;
    }

    /**
     * @return Vector[]
     */
    public function getContent(): array
    {
        return $this->vectors;
    }
}
