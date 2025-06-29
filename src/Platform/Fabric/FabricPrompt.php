<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Fabric;

/**
 * Represents a Fabric prompt pattern.
 */
final readonly class FabricPrompt implements FabricPromptInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $pattern,
        private string $content,
        private array $metadata = [],
    ) {
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
