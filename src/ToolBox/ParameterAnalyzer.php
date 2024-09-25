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

            if ('int' === $paramType) {
                $paramType = 'integer';
            }

            if ('float' === $paramType) {
                $paramType = 'number';
            }

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
