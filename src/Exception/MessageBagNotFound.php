<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\Exception;

final class MessageBagNotFound extends \DomainException
{
    public static function notFound(string $id): self
    {
        return new self(sprintf('MessageBag with id "%s" not found', $id));
    }
}
