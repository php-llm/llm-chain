<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

interface MessageInterface extends \JsonSerializable
{
    public function getRole(): Role;
}