<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model;

interface EmbeddingsModel extends Model
{
    public function supportsMultipleInputs(): bool;
}
