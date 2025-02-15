<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolConfigurationException;

final readonly class ToolAnalyzer
{
    public function __construct(
        private ParameterAnalyzer $parameterAnalyzer = new ParameterAnalyzer(),
    ) {
    }

    /**
     * @param class-string $className
     *
     * @return iterable<Metadata>
     */
    public function getMetadata(string $className): iterable
    {
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === count($attributes)) {
            throw ToolConfigurationException::missingAttribute($className);
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
