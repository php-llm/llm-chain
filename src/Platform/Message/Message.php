<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\FabricPattern\Pattern;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
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
     * Create a SystemMessage from a Fabric pattern.
     *
     * Requires the "php-llm/fabric-pattern" package to be installed.
     *
     * Note: Only one SystemMessage is supported per MessageBag. If you need to use
     * a Fabric pattern with an existing SystemMessage, you'll need to combine them manually.
     *
     * @throws \RuntimeException if fabric-pattern package is not installed
     */
    public static function fabric(string $pattern): SystemMessage
    {
        if (!class_exists(Pattern::class)) {
            throw new RuntimeException('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package: composer require php-llm/fabric-pattern');
        }

        $content = (new Pattern())->load($pattern);

        return new SystemMessage($content);
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
            static fn (\Stringable|string|ContentInterface $entry) => $entry instanceof ContentInterface ? $entry : (\is_string($entry) ? new Text($entry) : new Text((string) $entry)),
            $content,
        );

        return new UserMessage(...$content);
    }

    public static function ofToolCall(ToolCall $toolCall, string $content): ToolCallMessage
    {
        return new ToolCallMessage($toolCall, $content);
    }
}
