<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response\Metadata;

trait MetadataAwareTrait
{
    private ?Metadata $metadata = null;

    public function getMetadata(): Metadata
    {
        if (null === $this->metadata) {
            $this->metadata = new Metadata();
        }

        return $this->metadata;
    }
}
