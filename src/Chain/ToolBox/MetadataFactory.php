<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

interface MetadataFactory
{
    /**
     * @return iterable<Metadata>
     */
    public function getMetadata(mixed $reference): iterable;
}
