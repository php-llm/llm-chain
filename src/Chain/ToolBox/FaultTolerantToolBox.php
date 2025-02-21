<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Model\Response\ToolCall;

/**
 * Catches exceptions thrown by the inner tool box and returns error messages for the LLM instead.
 */
final readonly class FaultTolerantToolBox implements ToolBoxInterface
{
    public function __construct(
        private ToolBoxInterface $innerToolBox,
    ) {
    }

    public function getMap(): array
    {
        return $this->innerToolBox->getMap();
    }

    public function execute(ToolCall $toolCall): mixed
    {
        try {
            return $this->innerToolBox->execute($toolCall);
        } catch (ToolExecutionException $e) {
            return sprintf('An error occurred while executing tool "%s".', $e->toolCall->name);
        } catch (ToolNotFoundException) {
            $names = array_map(fn (Metadata $metadata) => $metadata->name, $this->getMap());

            return sprintf('Tool "%s" was not found, please use one of these: %s', $toolCall->name, implode(', ', $names));
        }
    }
}
