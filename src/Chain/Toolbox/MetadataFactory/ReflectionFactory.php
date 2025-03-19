<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory;

use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolMetadataException;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;

/**
 * Metadata factory that uses reflection in combination with `#[AsTool]` attribute to extract metadata from tools.
 */
final class ReflectionFactory extends AbstractFactory
{
    /**
     * @param class-string $reference
     */
    public function getMetadata(string $reference): iterable
    {
        if (!class_exists($reference)) {
            throw ToolMetadataException::invalidReference($reference);
        }

        $reflectionClass = new \ReflectionClass($reference);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === count($attributes)) {
            throw ToolMetadataException::missingAttribute($reference);
        }

        foreach ($attributes as $attribute) {
            yield $this->convertAttribute($reference, $attribute->newInstance());
        }
    }
}
