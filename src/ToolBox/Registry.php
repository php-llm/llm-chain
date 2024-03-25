<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type ParameterDefinition from ParameterAnalyzer
 * @phpstan-type ToolDefinition = array{
 *     type: 'function',
 *     function: array{
 *         name: string,
 *         description: string,
 *         parameters?: ParameterDefinition>
 *     }
 * }
 */
final class Registry
{
    private readonly array $tools;
    private array $map;

    public function __construct(
        private readonly ToolAnalyzer $toolAnalyzer,
        private readonly LoggerInterface $logger,
        iterable $tools,
    ) {
        $this->tools = $tools instanceof \Traversable ? iterator_to_array($tools) : $tools;
    }

    /**
     * @return list<ToolDefinition>
     */
    public function getMap(): array
    {
        if (isset($this->map)) {
            return $this->map;
        }

        $map = [];
        foreach ($this->tools as $tool) {
            foreach ($this->toolAnalyzer->getMetadata($tool::class) as $metadata) {
                $function = [
                    'name' => $metadata->name,
                    'description' => $metadata->description,
                ];

                if (isset($metadata->parameters)) {
                    $function['parameters'] = $metadata->parameters;
                }

                $map[] = [
                    'type' => 'function',
                    'function' => $function,
                ];
            }
        }

        return $this->map = $map;
    }

    public function execute(string $name, string $arguments): string
    {
        foreach ($this->tools as $tool) {
            foreach ($this->toolAnalyzer->getMetadata($tool::class) as $metadata) {
                if ($metadata->name === $name) {
                    $this->logger->debug(sprintf('Executing tool "%s" with "%s"', $name, $arguments));
                    return $tool->{$metadata->method}(...json_decode($arguments, true));
                }
            }
        }

        throw new \Exception('Tool not found');
    }
}
