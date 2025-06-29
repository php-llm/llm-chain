<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Response\ToolCall;
use Symfony\Component\Uid\UuidV7;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class ToolCallMessage implements MessageInterface
{
    public UuidV7 $uid;

    public function __construct(
        public ToolCall $toolCall,
        public string $content,
    ) {
        $this->uid = new UuidV7();
    }

    public function getRole(): Role
    {
        return Role::ToolCall;
    }

    public function getUid(): UuidV7
    {
        return $this->uid;
    }
}
