<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

interface Message extends \JsonSerializable
{
    public function getRole(): Role;

    public function isSystemMessage(): bool;

    public function isAssistantMessage(): bool;

    public function isUserMessage(): bool;

    public function isToolCallMessage(): bool;

    public function getMetadata(): Metadata;
}
