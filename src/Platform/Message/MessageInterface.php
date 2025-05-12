<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

interface MessageInterface
{
    public function getRole(): Role;
}
