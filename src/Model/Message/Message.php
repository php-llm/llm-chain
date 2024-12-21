<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Message\Content\Content;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use Symfony\Component\Uid\Uuid;

/**
 * Besides a base implementation this class is a factory for the specific message types.
 * We sacrifice basic OOP principles in favor of developer experience.
 */
abstract readonly class Message implements MessageInterface
{
    /**
     * Only available for subclasses.
     */
    protected function __construct(
        private Role $role,
        private Metadata $metadata = new Metadata(),
    ) {
    }

    public static function forSystem(string $content): SystemMessage
    {
        return new SystemMessage($content);
    }

    /**
     * @param ?ToolCall[] $toolCalls
     */
    public static function ofAssistant(?string $content = null, ?array $toolCalls = null, Metadata $metadata = new Metadata()): AssistantMessage
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

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
}
