<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class Claude implements LanguageModel
{
    public const VERSION_3_HAIKU = 'claude-3-haiku-20240307';
    public const VERSION_3_SONNET = 'claude-3-sonnet-20240229';
    public const VERSION_35_SONNET = 'claude-3-5-sonnet-20240620';
    public const VERSION_3_OPUS = 'claude-3-opus-20240229';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private string $version = self::VERSION_35_SONNET,
        private array $options = ['temperature' => 1.0, 'max_tokens' => 1000],
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
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
        return false; // it does, but implementation here is still open.
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }

    public function supportsToolCalling(): bool
    {
        return true;
    }
}
