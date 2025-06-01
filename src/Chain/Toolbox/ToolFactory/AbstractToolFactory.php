<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\ToolFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolConfigurationException;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactoryInterface;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Factory;
use PhpLlm\LlmChain\Platform\Tool\ExecutionReference;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
abstract class AbstractToolFactory implements ToolFactoryInterface
{
    public function __construct(
        private readonly Factory $factory = new Factory(),
    ) {
    }

    protected function convertAttribute(string $className, AsTool $attribute): Tool
    {
        try {
            return new Tool(
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
