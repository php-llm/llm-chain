<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory;

use PhpLlm\LlmChain\Chain\JsonSchema\Factory;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\ToolBox\ExecutionReference;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory;

abstract class AbstractFactory implements MetadataFactory
{
    public function __construct(
        private readonly Factory $factory = new Factory(),
    ) {
    }

    protected function convertAttribute(string $className, AsTool $attribute): Metadata
    {
        try {
            return new Metadata(
                new ExecutionReference($className, $attribute->method),
                $attribute->name,
                $attribute->description,
                $this->factory->buildParameters($className, $attribute->method)
            );
        } catch (\ReflectionException $e) {
            throw ToolConfigurationException::invalidMethod($className, $attribute->method, $e);
        }
    }
}
