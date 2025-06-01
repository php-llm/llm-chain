<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use OskarStark\Enum\Trait\Comparable;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
enum Role: string
{
    use Comparable;

    case System = 'system';
    case Assistant = 'assistant';
    case User = 'user';
    case ToolCall = 'tool';
}
