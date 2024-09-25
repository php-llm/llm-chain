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

namespace PhpLlm\LlmChain\Message;

enum Role: string
{
    case System = 'system';
    case Assistant = 'assistant';
    case User = 'user';
    case ToolCall = 'tool';
}
