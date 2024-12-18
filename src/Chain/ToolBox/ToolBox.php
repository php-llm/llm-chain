<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Exception\ToolNotFoundException;
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

    public function execute(ToolCall $toolCall): string
    {
        foreach ($this->tools as $tool) {
            foreach ($this->toolAnalyzer->getMetadata($tool::class) as $metadata) {
                if ($metadata->name === $toolCall->name) {
                    $result = $tool->{$metadata->method}(...$toolCall->arguments);

                    if ($result instanceof \JsonSerializable || is_array($result)) {
                        return json_encode($result, flags: JSON_THROW_ON_ERROR);
                    }

                    if (is_integer($result) || is_float($result)) {
                        return (string) $result;
                    }

                    return $result;
                }
            }
        }

        throw ToolNotFoundException::forToolCall($toolCall);
    }
}
