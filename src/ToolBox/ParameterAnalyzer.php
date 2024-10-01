<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\ToolBox\Attribute\ToolParameter;

/**
 * @phpstan-type ParameterDefinition array{
 *     type: 'object',
 *     properties: array<string, array{
 *         type: string,
 *         description: string,
 *         enum?: list<string>,
 *         const?: string|int|list<string>,
 *         pattern?: string,
 *         minLength?: int,
 *         maxLength?: int,
 *         minimum?: int,
 *         maximum?: int,
 *         multipleOf?: int,
 *         exclusiveMinimum?: int,
 *         exclusiveMaximum?: int,
 *         minItems?: int,
 *         maxItems?: int,
 *         uniqueItems?: bool,
 *         minContains?: int,
 *         maxContains?: int,
 *         required?: bool,
 *         minProperties?: int,
 *         maxProperties?: int,
 *         dependentRequired?: bool,
 *     }>,
 *     required: list<string>,
 * }
 */
final class ParameterAnalyzer
{
    /**
     * @return ParameterDefinition|null
     */
    public function getDefinition(string $className, string $methodName): ?array
    {
        $reflection = new \ReflectionMethod($className, $methodName);
        $parameters = $reflection->getParameters();

        if (0 === count($parameters)) {
            return null;
        }

        $result = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        foreach ($parameters as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();
            $paramType = $paramType instanceof \ReflectionNamedType ? $paramType->getName() : 'mixed';

            $paramType = match ($paramType) {
                'int' => 'integer',
                'float' => 'number',
                default => $paramType,
            };

            if (!$parameter->isOptional()) {
                $result['required'][] = $paramName;
            }

            $property = [
                'type' => $paramType,
                'description' => $this->getParameterDescription($reflection, $paramName),
            ];

            // Check for ToolParameter attributes
            $attributes = $parameter->getAttributes(ToolParameter::class);
            if (count($attributes) > 0) {
                $attributeState = array_filter((array) $attributes[0]->newInstance(), fn ($value) => null !== $value);
                $property = array_merge($property, $attributeState);
            }

            $result['properties'][$paramName] = $property;
        }

        return $result;
    }

    private function getParameterDescription(\ReflectionMethod $method, string $paramName): string
    {
        $docComment = $method->getDocComment();
        if (!$docComment) {
            return '';
        }

        $pattern = '/@param\s+\S+\s+\$'.preg_quote($paramName, '/').'\s+(.*)/';
        if (preg_match($pattern, $docComment, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }
}
