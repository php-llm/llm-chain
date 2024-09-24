<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Response\ToolCall;
use Webmozart\Assert\Assert;

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
        Assert::minCount($content, 1, 'At least a single content part must be given.');

        $text = null;
        $images = [];
        foreach ($content as $index => $entry) {
            if (0 === $index) {
                $text = $entry;

                if (\is_string($text)) {
                    $text = new Text($entry);
                }

                if (!$text instanceof Text) {
                    throw new \InvalidArgumentException('The first content piece has to be a string or Text part.');
                }

                continue;
            }

            if (!is_string($entry) && !$entry instanceof Image) {
                continue;
            }

            $images[] = \is_string($entry) ? new Image($entry) : $entry;
        }

        return new UserMessage($text, ...$images);
    }

    public static function ofToolCall(ToolCall $toolCall, string $content): ToolCallMessage
    {
        return new ToolCallMessage($toolCall, $content);
    }
}
