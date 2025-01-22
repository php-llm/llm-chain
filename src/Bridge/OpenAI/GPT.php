<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\LanguageModel;

final class GPT implements LanguageModel
{
    public const GPT_35_TURBO = 'gpt-3.5-turbo';
    public const GPT_35_TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';
    public const GPT_4 = 'gpt-4';
    public const GPT_4_TURBO = 'gpt-4-turbo';
    public const GPT_4O = 'gpt-4o';
    public const GPT_4O_MINI = 'gpt-4o-mini';
    public const GPT_4O_AUDIO = 'gpt-4o-audio-preview';
    public const O1_MINI = 'o1-mini';
    public const O1_PREVIEW = 'o1-preview';

    /**
     * @param array<mixed> $options The default options for the model usage
     */
    public function __construct(
        private readonly string $version = self::GPT_4O,
        private readonly array $options = ['temperature' => 1.0],
        private bool $supportsAudioInput = false,
        private bool $supportsImageInput = false,
        private bool $supportsStructuredOutput = false,
    ) {
        if (false === $this->supportsAudioInput) {
            $this->supportsAudioInput = self::GPT_4O_AUDIO === $this->version;
        }

        if (false === $this->supportsImageInput) {
            $this->supportsImageInput = in_array($this->version, [self::GPT_4_TURBO, self::GPT_4O, self::GPT_4O_MINI, self::O1_MINI, self::O1_PREVIEW], true);
        }

        if (false === $this->supportsStructuredOutput) {
            $this->supportsStructuredOutput = in_array($this->version, [self::GPT_4O, self::GPT_4O_MINI], true);
        }
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
        return $this->supportsAudioInput;
    }

    public function supportsImageInput(): bool
    {
        return $this->supportsImageInput;
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return $this->supportsStructuredOutput;
    }

    public function supportsToolCalling(): bool
    {
        return true;
    }
}
