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
}
