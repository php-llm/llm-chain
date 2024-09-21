<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

/**
 * @phpstan-import-type ParameterDefinition from ParameterAnalyzer
 *
 * @phpstan-type ToolDefinition = array{
 *      type: 'function',
 *      function: array{
 *          name: string,
 *          description: string,
 *          parameters?: ParameterDefinition
 *      }
 *  }
 */
interface RegistryInterface
{
    /**
     * @return list<ToolDefinition>
     */
    public function getMap(): array;

    public function execute(string $name, string $arguments): string;
}
