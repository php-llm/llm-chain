<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Event;

use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * Dispatched after the arguments are denormalized, just before invoking the tool.
 *
 * @author Valtteri R <valtzu@gmail.com>
 */
final readonly class ToolCallArgumentsResolved
{
    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        public object $tool,
        public Tool $metadata,
        public array $arguments,
    ) {
    }
}
