<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Fabric;

/**
 * Interface for Fabric prompt patterns.
 */
interface FabricPromptInterface
{
    /**
     * Get the pattern name (e.g., 'create_summary').
     *
     * @return non-empty-string
     */
    public function getPattern(): string;

    /**
     * Get the system prompt content.
     */
    public function getContent(): string;

    /**
     * Get metadata about the pattern.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array;
}
