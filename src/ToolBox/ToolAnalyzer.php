<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\ToolBox;

use SymfonyLlm\LlmChain\Exception\InvalidToolImplementation;

final class ToolAnalyzer
{
    public function __construct(
        private readonly ParameterAnalyzer $parameterAnalyzer,
    ) {
    }

    /**
     * @return \Generator<Metadata>
     */
    public function getMetadata(string $className): \Generator
    {
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === count($attributes)) {
            throw InvalidToolImplementation::missingAttribute($className);
        }

        foreach ($attributes as $attribute) {
            yield $this->convertAttribute($className, $attribute->newInstance());
        }
    }

    private function convertAttribute(string $className, AsTool $attribute): Metadata
    {
        return new Metadata(
            $className,
            $attribute->name,
            $attribute->description,
            $attribute->method,
            $this->parameterAnalyzer->getDefinition($className, $attribute->method)
        );
    }
}
