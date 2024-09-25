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

use PhpLlm\LlmChain\Response\ToolCall;

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
                    return $tool->{$metadata->method}(...$toolCall->arguments);
                }
            }
        }

        throw new \Exception('Tool not found');
    }
}
