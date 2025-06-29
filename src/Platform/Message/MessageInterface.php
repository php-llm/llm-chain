<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use Symfony\Component\Uid\UuidV7;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
interface MessageInterface
{
    public function getRole(): Role;

    public function getUid(): UuidV7;
}
