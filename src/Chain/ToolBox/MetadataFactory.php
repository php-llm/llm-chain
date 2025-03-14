<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolMetadataException;

interface MetadataFactory
{
    /**
     * @return iterable<Metadata>
     *
     * @throws ToolMetadataException if the metadata for the given reference is not found
     */
    public function getMetadata(string $reference): iterable;
}
