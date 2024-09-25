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

use PhpLlm\LlmChain\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Response\ToolCall;

final readonly class Message
{
    // Disabled by default, just a bridge to the specific messages
    private function __construct()
    {
    }

    public static function forSystem(string $content): SystemMessage
    {
        return new SystemMessage($content);
    }

    /**
     * @param ?ToolCall[] $toolCalls
     */
    public static function ofAssistant(?string $content = null, ?array $toolCalls = null): AssistantMessage
    {
        return new AssistantMessage($content, $toolCalls);
    }

    public static function ofUser(string|ContentInterface ...$content): UserMessage
    {
        $content = \array_map(
            static fn (string|ContentInterface $entry) => \is_string($entry) ? new Text($entry) : $entry,
            $content,
        );

        return new UserMessage(...$content);
    }

    public static function ofToolCall(ToolCall $toolCall, string $content): ToolCallMessage
    {
        return new ToolCallMessage($toolCall, $content);
    }
}
