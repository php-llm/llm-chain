<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Response\ToolCall;
use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class AssistantMessage implements MessageInterface
{
    /**
     * @param ?ToolCall[] $toolCalls
     */
    public function __construct(
        public ?string $content = null,
        public ?array $toolCalls = null,
    ) {
    }

    public function getRole(): Role
    {
        return Role::Assistant;
    }

    public function getId(): Uuid
    {
        // Generate deterministic UUID based on content, role, and tool calls
        $toolCallsData = '';
        if ($this->toolCalls !== null) {
            $toolCallsData = serialize(array_map(
                static fn (ToolCall $toolCall) => [
                    'id' => $toolCall->id,
                    'name' => $toolCall->name,
                    'arguments' => $toolCall->arguments,
                ],
                $this->toolCalls
            ));
        }
        
        $data = sprintf('assistant:%s:%s', $this->content ?? '', $toolCallsData);
        
        return Uuid::v5(self::getNamespace(), $data);
    }

    private static function getNamespace(): Uuid
    {
        // Use a fixed namespace UUID for the LLM Chain message system
        // This ensures deterministic IDs across application runs
        return Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
    }

    public function hasToolCalls(): bool
    {
        return null !== $this->toolCalls && 0 !== \count($this->toolCalls);
    }
}
