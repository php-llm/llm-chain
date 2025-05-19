<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock\Nova;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class Nova implements LanguageModel
{
    public const MICRO = 'nova-micro';
    public const LITE = 'nova-lite';
    public const PRO = 'nova-pro';
    public const PREMIER = 'nova-premier';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private string $name = self::PRO,
        private array $options = ['temperature' => 1.0, 'max_tokens' => 1000],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function supportsAudioInput(): bool
    {
        return false;
    }

    public function supportsImageInput(): bool
    {
        return true;
    }

    public function supportsStreaming(): bool
    {
        return false;
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }

    public function supportsToolCalling(): bool
    {
        // Tool calling is supported but:
        // Invoke currently has some validation errors on the bedrock api side when returning tool calling results.
        // Its encouraged to use the converse api instead.
        return false;
    }
}
