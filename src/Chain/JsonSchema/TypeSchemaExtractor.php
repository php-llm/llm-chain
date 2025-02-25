<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\JsonSchema;

/**
 * @internal
 */
final readonly class TypeSchemaExtractor
{
    /**
     * @return array{type: string, items?: array{type: string}}
     */
    public function fromProperty(\ReflectionProperty $property): array
    {
        $type = $property->getType();
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';

        if ('array' === $typeName) {
            return ['type' => 'array', 'items' => ['type' => $this->getArrayFromProperty($property)]];
        }

        return $this->convertTypes($typeName);
    }

    /**
     * @return array{type: string, items?: array{type: string}}
     */
    public function fromParameter(\ReflectionParameter $parameter): array
    {
        $type = $parameter->getType();
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';

        if ('array' === $typeName) {
            return ['type' => 'array', 'items' => ['type' => $this->getArrayFromParameter($parameter)]];
        }

        return $this->convertTypes($typeName);
    }

    /**
     * @return array{type: string, format?: string}
     */
    private function convertTypes(string $type): array
    {
        if (\DateTimeInterface::class === $type || is_subclass_of($type, \DateTimeInterface::class)) {
            return ['type' => 'string', 'format' => 'date-time'];
        }

        return ['type' => $this->convertScalarType($type)];
    }

    private function convertScalarType(string $type): string
    {
        return match ($type) {
            'int' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
            'string' => 'string',
            default => throw new \InvalidArgumentException(sprintf('Unknown scalar type "%s"', $type)),
        };
    }

    private function getArrayFromProperty(\ReflectionProperty $property): string
    {
        $comment = $property->getDocComment();
        $class = $property->getDeclaringClass();

        if (false === $comment && $class->hasMethod('__construct')) {
            return $this->getArrayFromParameter(
                new \ReflectionParameter([$class->getName(), '__construct'], $property->getName())
            );
        }

        if (preg_match('/@var\s+list<([a-zA-Z]+)>\s+/', $comment, $matches)) {
            return $this->convertScalarType(trim($matches[1]));
        }

        if (preg_match('/@var\s+([a-zA-Z]+)\[\]\s+/', $comment, $matches)) {
            return $this->convertScalarType(trim($matches[1]));
        }

        return '';
    }

    private function getArrayFromParameter(\ReflectionParameter $parameter): string
    {
        $comment = $parameter->getDeclaringFunction()->getDocComment();

        if (preg_match('/@param\s+list<([a-zA-Z]+)>\s+\$'.preg_quote($parameter->getName(), '/').'\s+/', $comment, $matches)) {
            return $this->convertScalarType(trim($matches[1]));
        }

        if (preg_match('/@param\s+([a-zA-Z]+)\[\]\s+\$'.preg_quote($parameter->getName(), '/').'\s+/', $comment, $matches)) {
            return $this->convertScalarType(trim($matches[1]));
        }

        return '';
    }
}
