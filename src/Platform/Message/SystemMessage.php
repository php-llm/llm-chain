<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class SystemMessage implements MessageInterface
{
    public function __construct(public string $content)
    {
    }

    public function getRole(): Role
    {
        return Role::System;
    }

    public function getUid(): string
    {
        // Generate deterministic UID based on content and role
        $data = sprintf('system:%s', $this->content);
        
        return hash('sha256', $data);
    }
}
