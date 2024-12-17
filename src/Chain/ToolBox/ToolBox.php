<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Response\ToolCall;

final class ToolBox implements ToolBoxInterface
{
    /**
     * @var array<object>
     */
    private readonly array $tools;

    /**
     * @var Metadata[]
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

        foreach ($this->tools as $key => $tool) {
            foreach ($this->toolAnalyzer->getMetadata($key, $tool::class) as $metadata) {
                $map[] = $metadata;
            }
        }

        return $this->map = $map;
    }

    public function execute(ToolCall $toolCall): string
    {
        foreach ($this->tools as $key => $tool) {
            foreach ($this->toolAnalyzer->getMetadata($key, $tool::class) as $metadata) {
                if ($metadata->name === $toolCall->name) {
                    return $tool->{$metadata->method}(...$toolCall->arguments);
                }
            }
        }

        if (isset($this->tools[$toolCall->name])) {
            return $this->tools[$toolCall->name]->call(...$toolCall->arguments);
        }

        throw new RuntimeException('Tool not found');
    }
}
