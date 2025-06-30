<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
interface MessageInterface
{
    public function getRole(): Role;

    public function getId(): Uuid;
}
