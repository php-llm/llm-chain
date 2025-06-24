<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Response\ToolCall;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class Message
{
    // Disabled by default, just a bridge to the specific messages
    private function __construct()
    {
    }

    public static function forSystem(\Stringable|string $content): SystemMessage
    {
        return new SystemMessage($content instanceof \Stringable ? (string) $content : $content);
    }

    /**
     * @param ?ToolCall[] $toolCalls
     */
    public static function ofAssistant(?string $content = null, ?array $toolCalls = null): AssistantMessage
    {
        return new AssistantMessage($content, $toolCalls);
    }

    public static function ofUser(\Stringable|string|ContentInterface ...$content): UserMessage
    {
        $content = array_map(
            static fn (string|ContentInterface $entry) => \is_string($entry) ? new Text($entry) : ($entry instanceof \Stringable ? new Text((string) $entry) : $entry),
            $content,
        );

        return new UserMessage(...$content);
    }

    public static function ofToolCall(ToolCall $toolCall, string $content): ToolCallMessage
    {
        return new ToolCallMessage($toolCall, $content);
    }
}
