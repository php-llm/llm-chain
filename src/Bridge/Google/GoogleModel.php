<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class GoogleModel implements LanguageModel
{
    public const GEMINI_2_FLASH = 'gemini-2.0-flash';
    public const GEMINI_2_PRO = 'gemini-2.0-pro-exp-02-05';
    public const GEMINI_2_FLASH_LITE = 'gemini-2.0-flash-lite-preview-02-05';
    public const GEMINI_2_FLASH_THINKING = 'gemini-2.0-flash-thinking-exp-01-21';
    public const GEMINI_1_5_FLASH = 'gemini-1.5-flash';

    /**
     * @param array<string, mixed> $options The default options for the model usage
     */
    public function __construct(
        private string $version = self::GEMINI_2_PRO,
        private array $options = ['temperature' => 1.0],
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
        return false; // it does, but implementation here is still open; in_array($this->version, [self::GEMINI_2_FLASH, self::GEMINI_2_PRO, self::GEMINI_1_5_FLASH], true);
    }

    public function supportsImageInput(): bool
    {
        return false; // it does, but implementation here is still open;in_array($this->version, [self::GEMINI_2_FLASH, self::GEMINI_2_PRO, self::GEMINI_2_FLASH_LITE, self::GEMINI_2_FLASH_THINKING, self::GEMINI_1_5_FLASH], true);
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
        return false;
    }
}
