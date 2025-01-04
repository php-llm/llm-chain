<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Exception\ToolBoxException;
use PhpLlm\LlmChain\Model\Response\ToolCall;

final class ToolBox implements ToolBoxInterface
{
    /**
     * @var list<object>
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
        foreach ($this->tools as $tool) {
            foreach ($this->toolAnalyzer->getMetadata($tool::class) as $metadata) {
                $map[] = $metadata;
            }
        }

        return $this->map = $map;
    }

    public function execute(ToolCall $toolCall): mixed
    {
        foreach ($this->tools as $tool) {
            foreach ($this->toolAnalyzer->getMetadata($tool::class) as $metadata) {
                if ($metadata->name !== $toolCall->name) {
                    continue;
                }

                try {
                    $result = $tool->{$metadata->method}(...$toolCall->arguments);
                } catch (\Throwable $e) {
                    throw ToolBoxException::executionFailed($toolCall, $e);
                }

                return $result;
            }
        }

        throw ToolBoxException::notFoundForToolCall($toolCall);
    }
}
