<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

interface MessageInterface extends \JsonSerializable
{
    public function getRole(): Role;

    public function accept(MessageVisitor $visitor): array;
}
