<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class Claude implements LanguageModel
{
    public const HAIKU_3 = 'claude-3-haiku-20240307';
    public const HAIKU_35 = 'claude-3-5-haiku-20241022';
    public const SONNET_3 = 'claude-3-sonnet-20240229';
    public const SONNET_35 = 'claude-3-5-sonnet-20240620';
    public const SONNET_35_V2 = 'claude-3-5-sonnet-20241022';
    public const SONNET_37 = 'claude-3-7-sonnet-20250219';
    public const OPUS_3 = 'claude-3-opus-20240229';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private string $name = self::SONNET_37,
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
