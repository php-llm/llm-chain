<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class SystemMessage implements MessageInterface
{
    public function __construct(public string $content)
    {
    }

    public function getRole(): Role
    {
        return Role::System;
    }

    public function getId(): Uuid
    {
        // Generate deterministic UUID based on content and role
        $data = sprintf('system:%s', $this->content);
        
        return Uuid::v5(self::getNamespace(), $data);
    }

    private static function getNamespace(): Uuid
    {
        // Use a fixed namespace UUID for the LLM Chain message system
        // This ensures deterministic IDs across application runs
        return Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
    }
}
