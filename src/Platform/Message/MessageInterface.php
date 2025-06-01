<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
interface MessageInterface
{
    public function getRole(): Role;
}
