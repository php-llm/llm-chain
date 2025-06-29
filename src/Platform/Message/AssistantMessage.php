<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Response\ToolCall;
use Symfony\Component\Uid\UuidV7;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class AssistantMessage implements MessageInterface
{
    public UuidV7 $uid;

    /**
     * @param ?ToolCall[] $toolCalls
     */
    public function __construct(
        public ?string $content = null,
        public ?array $toolCalls = null,
    ) {
        $this->uid = new UuidV7();
    }

    public function getRole(): Role
    {
        return Role::Assistant;
    }

    public function getUid(): UuidV7
    {
        return $this->uid;
    }

    public function hasToolCalls(): bool
    {
        return null !== $this->toolCalls && 0 !== \count($this->toolCalls);
    }
}
