<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\JsonSchema;

/**
 * @internal
 */
final readonly class DescriptionParser
{
    public function fromProperty(\ReflectionProperty $property): string
    {
        $comment = $property->getDocComment();

        if (is_string($comment) && preg_match('/@var\s+[a-zA-Z\\\\]+\s+((.*)(?=\*)|.*)/', $comment, $matches)) {
            return trim($matches[1]);
        }

        $class = $property->getDeclaringClass();
        if ($class->hasMethod('__construct')) {
            return $this->fromParameter(
                new \ReflectionParameter([$class->getName(), '__construct'], $property->getName())
            );
        }

        return '';
    }

    public function fromParameter(\ReflectionParameter $parameter): string
    {
        $comment = $parameter->getDeclaringFunction()->getDocComment();
        if (!$comment) {
            return '';
        }

        if (preg_match('/@param\s+\S+\s+\$'.preg_quote($parameter->getName(), '/').'\s+((.*)(?=\*)|.*)/', $comment, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }
}
