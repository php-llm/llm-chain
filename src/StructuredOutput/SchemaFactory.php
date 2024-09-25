<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\StructuredOutput;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;

final readonly class SchemaFactory
{
    public function __construct(
        private PropertyInfoExtractor $propertyInfo,
    ) {
    }

    public static function create(): self
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        return new self(
            new PropertyInfoExtractor(
                [$reflectionExtractor],
                [$phpDocExtractor, $reflectionExtractor],
                [$phpDocExtractor],
                [$reflectionExtractor],
            )
        );
    }

    /**
     * @param class-string $className
     *
     * @return array<string, mixed>
     */
    public function buildSchema(string $className): array
    {
        $reflectionClass = new \ReflectionClass($className);
        $properties = $reflectionClass->getProperties();

        $schema = [
            'title' => $reflectionClass->getShortName(),
            'type' => 'object',
            'properties' => [],
            'required' => [],
            'additionalProperties' => false,
        ];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $types = $this->propertyInfo->getTypes($className, $propertyName);
            $description = $this->propertyInfo->getShortDescription($className, $propertyName);

            if (empty($types)) {
                // Skip if no type info is available
                continue;
            }

            // Assume the first type is the main type (ignore union types for simplicity)
            $type = $types[0];
            $propertySchema = $this->getTypeSchema($type);

            // Add description if available
            if ($description) {
                $propertySchema['description'] = $description;
            }

            // Add property schema to main schema
            $schema['properties'][$propertyName] = $propertySchema;

            // If the property does not allow null, mark it as required
            if (!$type->isNullable()) {
                $schema['required'][] = $propertyName;
            }
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function getTypeSchema(Type $type): array
    {
        switch ($type->getBuiltinType()) {
            case Type::BUILTIN_TYPE_INT:
                return ['type' => 'integer'];

            case Type::BUILTIN_TYPE_FLOAT:
                return ['type' => 'number'];

            case Type::BUILTIN_TYPE_BOOL:
                return ['type' => 'boolean'];

            case Type::BUILTIN_TYPE_ARRAY:
                $collectionValueTypes = $type->getCollectionValueTypes();

                if (!empty($collectionValueTypes) && Type::BUILTIN_TYPE_OBJECT === $collectionValueTypes[0]->getBuiltinType()) {
                    return [
                        'type' => 'array',
                        'items' => $this->buildSchema($collectionValueTypes[0]->getClassName()),
                    ];
                } elseif (!empty($collectionValueTypes)) {
                    return [
                        'type' => 'array',
                        'items' => $this->getTypeSchema($collectionValueTypes[0]),
                    ];
                }

                // Fallback for arrays
                return ['type' => 'array', 'items' => ['type' => 'string']];

            case Type::BUILTIN_TYPE_OBJECT:
                if (\DateTimeInterface::class === $type->getClassName()) {
                    return ['type' => 'string', 'format' => 'date-time'];
                } else {
                    // Recursively build the schema for an object type
                    return $this->buildSchema($type->getClassName());
                }

                // no break
            case Type::BUILTIN_TYPE_STRING:
            default:
                // Fallback to string for any unhandled types
                return ['type' => 'string'];
        }
    }
}
