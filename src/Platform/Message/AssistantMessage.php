<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Response\ToolCall;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\TimeBasedUidInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class AssistantMessage implements MessageInterface
{
    public AbstractUid&TimeBasedUidInterface $id;

    /**
     * @param ?ToolCall[] $toolCalls
     */
    public function __construct(
        public ?string $content = null,
        public ?array $toolCalls = null,
    ) {
        $this->id = Uuid::v7();
    }

    public function getRole(): Role
    {
        return Role::Assistant;
    }

    public function getId(): AbstractUid&TimeBasedUidInterface
    {
        return $this->id;
    }

    public function hasToolCalls(): bool
    {
        return null !== $this->toolCalls && 0 !== \count($this->toolCalls);
    }
}
