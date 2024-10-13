<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Message\Content\Content;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Response\ToolCall;

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

    public static function ofUser(string|Content ...$content): UserMessage
    {
        $content = \array_map(
            static fn (string|Content $entry) => \is_string($entry) ? new Text($entry) : $entry,
            $content,
        );

        return new UserMessage(...$content);
    }

    public static function ofToolCall(ToolCall $toolCall, string $content): ToolCallMessage
    {
        return new ToolCallMessage($toolCall, $content);
    }
}
