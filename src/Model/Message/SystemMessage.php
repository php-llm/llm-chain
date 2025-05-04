<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

final readonly class SystemMessage implements MessageInterface
{
    public function __construct(public string $content)
    {
    }

    public function getRole(): Role
    {
        return Role::System;
    }
}
