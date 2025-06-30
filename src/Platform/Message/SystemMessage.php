<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class SystemMessage implements MessageInterface
{
    public Uuid $id;

    public function __construct(public string $content)
    {
        $this->id = Uuid::v7();
    }

    public function getRole(): Role
    {
        return Role::System;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}
