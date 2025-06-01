<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response\Metadata;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
trait MetadataAwareTrait
{
    private ?Metadata $metadata = null;

    public function getMetadata(): Metadata
    {
        return $this->metadata ??= new Metadata();
    }
}
