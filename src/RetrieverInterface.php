<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;

interface RetrieverInterface
{
    public function enrich(string $search): string;
}
