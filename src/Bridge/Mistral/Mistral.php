<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Mistral;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class Mistral implements LanguageModel
{
    public const CODESTRAL = 'codestral-latest';
    public const CODESTRAL_MAMBA = 'open-codestral-mamba';
    public const MISTRAL_LARGE = 'mistral-large-latest';
    public const MISTRAL_SMALL = 'mistral-small-latest';
    public const MISTRAL_NEMO = 'open-mistral-nemo';
    public const MISTRAL_SABA = 'mistral-saba-latest';
    public const MINISTRAL_3B = 'mistral-3b-latest';
    public const MINISTRAL_8B = 'mistral-8b-latest';
    public const PIXSTRAL_LARGE = 'pixstral-large-latest';
    public const PIXSTRAL = 'pixstral-12b-latest';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $name = self::MISTRAL_LARGE,
        private array $options = [],
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
        return in_array($this->name, [self::PIXSTRAL, self::PIXSTRAL_LARGE, self::MISTRAL_SMALL], true);
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return true;
    }

    public function supportsToolCalling(): bool
    {
        return in_array($this->name, [
            self::CODESTRAL,
            self::MISTRAL_LARGE,
            self::MISTRAL_SMALL,
            self::MISTRAL_NEMO,
            self::MINISTRAL_3B,
            self::MINISTRAL_8B,
            self::PIXSTRAL,
            self::PIXSTRAL_LARGE,
        ], true);
    }
}
