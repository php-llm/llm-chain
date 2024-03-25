<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

/**
 * @phpstan-type ParameterDefinition array{
 *     type: 'object',
 *     properties: array<string, array{type: string, description: string}>,
 *     required: list<string>,
 * }
 */
final class ParameterAnalyzer
{
    /**
     * @return ParameterDefinition|null
     */
    public function getDefinition(string $className, string $methodName): array|null
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
            $paramType = $parameter->getType() ? $parameter->getType()->getName() : 'mixed';

            if (!$parameter->isOptional()) {
                $result['required'][] = $paramName;
            }

            $result['properties'][$paramName] = [
                'type' => $paramType,
                'description' => $this->getParameterDescription($reflection, $paramName),
            ];
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
