<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory;

use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory;

/**
 * Metadata factory that uses reflection in combination with `#[AsTool]` attribute to extract metadata from tools.
 */
final readonly class ReflectionFactory implements MetadataFactory
{
    public function __construct(
        private Factory $factory = new Factory(),
    ) {
    }

    /**
     * @return iterable<Metadata>
     */
    public function getMetadata(mixed $reference): iterable
    {
        if (!is_object($reference) && !is_string($reference) || is_string($reference) && !class_exists($reference)) {
            throw ToolConfigurationException::invalidReference($reference);
        }

        $reflectionClass = new \ReflectionClass($reference);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === count($attributes)) {
            throw ToolConfigurationException::missingAttribute($reflectionClass->getName());
        }

        foreach ($attributes as $attribute) {
            yield $this->convertAttribute($reflectionClass->getName(), $attribute->newInstance());
        }
    }

    private function convertAttribute(string $className, AsTool $attribute): Metadata
    {
        try {
            return new Metadata(
                $className,
                $attribute->name,
                $attribute->description,
                $attribute->method,
                $this->factory->buildParameters($className, $attribute->method)
            );
        } catch (\ReflectionException) {
            throw ToolConfigurationException::invalidMethod($className, $attribute->method);
        }
    }
}
