<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Exception\InvalidToolImplementation;

final readonly class ToolAnalyzer
{
    public function __construct(
        private ParameterAnalyzer $parameterAnalyzer = new ParameterAnalyzer(),
    ) {
    }

    /**
     * @param int|string $toolKey
     * @param class-string $className
     *
     * @return iterable<Metadata>
     */
    public function getMetadata(string|int $toolKey, string $className): iterable
    {
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === \count($attributes)) {
            if (true !== is_string($toolKey)) {
                throw new InvalidToolImplementation('Use AsTool attribute to configure your tools or create your toolBox like "new ToolBox([\'toolName\' => $toolInstance]")');
            }

            if (false === $reflectionClass->hasMethod('__invoke')) {
                throw new InvalidToolImplementation('The tool must implement the __invoke() method');
            }

            yield $this->createToolMetaData($className, $toolKey);
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

    private function createToolMetaData(string $className, string $toolKey): Metadata
    {
        return new Metadata(
            $className,
            $toolKey,
            'Use "AsTool" attribute to add description',
            '__invoke',
            $this->parameterAnalyzer->getDefinition($className, '__invoke')
        );
    }
}
