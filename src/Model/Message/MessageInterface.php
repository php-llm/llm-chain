<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

interface MessageInterface
{
    public function getRole(): Role;
}
