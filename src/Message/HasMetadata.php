<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

trait HasMetadata
{
    private readonly Metadata $metadata;

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
}
