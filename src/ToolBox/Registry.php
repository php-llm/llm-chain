<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Response\ToolCall;

/**
 * @phpstan-import-type ToolDefinition from RegistryInterface
 */
final class Registry implements RegistryInterface
{
    /**
     * @var list<object>
     */
    private readonly array $tools;

    /**
     * @var list<ToolDefinition>
     */
    private array $map;

    /**
     * @param iterable<object> $tools
     */
    public function __construct(
        private readonly ToolAnalyzer $toolAnalyzer,
        iterable $tools,
    ) {
        $this->tools = $tools instanceof \Traversable ? iterator_to_array($tools) : $tools;
    }

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

    public function execute(ToolCall $toolCall): string
    {
        foreach ($this->tools as $tool) {
            foreach ($this->toolAnalyzer->getMetadata($tool::class) as $metadata) {
                if ($metadata->name === $toolCall->name) {
                    return $tool->{$metadata->method}(...$toolCall->arguments);
                }
            }
        }

        throw new \Exception('Tool not found');
    }
}
