<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use OskarStark\Enum\Trait\Comparable;

enum Role: string
{
    use Comparable;

    case System = 'system';
    case Assistant = 'assistant';
    case User = 'user';
    case ToolCall = 'tool';
}
