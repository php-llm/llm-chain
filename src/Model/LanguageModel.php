<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model;

interface LanguageModel extends Model
{
    public function supportsImageInput(): bool;

    public function supportsStreaming(): bool;

    public function supportsStructuredOutput(): bool;

    public function supportsToolCalling(): bool;
}
