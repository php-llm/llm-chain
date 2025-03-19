<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolMetadataException;

interface MetadataFactory
{
    /**
     * @return iterable<Metadata>
     *
     * @throws ToolMetadataException if the metadata for the given reference is not found
     */
    public function getMetadata(string $reference): iterable;
}
