<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\JsonSchema;

use PhpLlm\LlmChain\Chain\JsonSchema\Attribute\With;

/**
 * @internal
 *
 * @phpstan-type JsonSchema array{
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
 *     additionalProperties: false,
 * }
 */
final readonly class Factory
{
    public function __construct(
        private DescriptionParser $descriptionParser = new DescriptionParser(),
        private TypeSchemaExtractor $typeSchemaExtractor = new TypeSchemaExtractor(),
    ) {
    }

    /**
     * @return JsonSchema|null
     */
    public function buildParameters(string $className, string $methodName): ?array
    {
        $reflection = new \ReflectionMethod($className, $methodName);

        return $this->convertTypes($reflection->getParameters());
    }

    /**
     * @return JsonSchema|null
     */
    public function buildProperties(string $className): ?array
    {
        $reflection = new \ReflectionClass($className);

        return $this->convertTypes($reflection->getProperties());
    }

    /**
     * @param list<\ReflectionProperty|\ReflectionParameter> $elements
     *
     * @return JsonSchema|null
     */
    private function convertTypes(array $elements): ?array
    {
        if (0 === count($elements)) {
            return null;
        }

        $result = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
            'additionalProperties' => false,
        ];

        foreach ($elements as $element) {
            $name = $element->getName();
            $reflectionType = $element->getType();

            $schema = $element instanceof \ReflectionParameter
                ? $this->typeSchemaExtractor->fromParameter($element)
                : $this->typeSchemaExtractor->fromProperty($element);

            if ($element instanceof \ReflectionProperty && $reflectionType->allowsNull()) {
                $schema['type'] = [$schema['type'], 'null'];
            }

            if ($element instanceof \ReflectionProperty && !$reflectionType->allowsNull()) {
                $result['required'][] = $name;
            }

            if ($element instanceof \ReflectionParameter && !$element->isOptional()) {
                $result['required'][] = $name;
            }

            $description = $element instanceof \ReflectionParameter
                ? $this->descriptionParser->fromParameter($element)
                : $this->descriptionParser->fromProperty($element);
            if ('' !== $description) {
                $schema['description'] = $description;
            }

            // Check for ToolParameter attributes
            $attributes = $element->getAttributes(With::class);
            if (count($attributes) > 0) {
                $attributeState = array_filter((array) $attributes[0]->newInstance(), fn ($value) => null !== $value);
                $schema = array_merge($schema, $attributeState);
            }

            $result['properties'][$name] = $schema;
        }

        return $result;
    }
}
