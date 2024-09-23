<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Exception;

final class MessageBagNotFound extends \DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('MessageBag with id "%s" not found', $id));
    }
}
