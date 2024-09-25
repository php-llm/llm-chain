<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Exception\InvalidToolImplementation;

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
