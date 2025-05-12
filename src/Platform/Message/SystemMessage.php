<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

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
