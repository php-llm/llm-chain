<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Meta;

use PhpLlm\LlmChain\Model\LanguageModel;

final readonly class Llama implements LanguageModel
{
    public const LLAMA_3_2_90B_VISION_INSTRUCT = 'llama-3.2-90b-vision-instruct';
    public const LLAMA_3_2_11B_VISION_INSTRUCT = 'llama-3.2-11b-vision-instruct';
    public const LLAMA_3_2_3B = 'llama-3.2-3b';
    public const LLAMA_3_2_3B_INSTRUCT = 'llama-3.2-3b-instruct';
    public const LLAMA_3_2_1B = 'llama-3.2-1b';
    public const LLAMA_3_2_1B_INSTRUCT = 'llama-3.2-1b-instruct';
    public const LLAMA_3_1_405B_INSTRUCT = 'llama-3.1-405b-instruct';
    public const LLAMA_3_1_70B = 'llama-3.1-70b';
    public const LLAMA_3_1_70B_INSTRUCT = 'llama-3-70b-instruct';
    public const LLAMA_3_1_8B = 'llama-3.1-8b';
    public const LLAMA_3_1_8B_INSTRUCT = 'llama-3.1-8b-instruct';
    public const LLAMA_3_70B = 'llama-3-70b';
    public const LLAMA_3_8B_INSTRUCT = 'llama-3-8b-instruct';
    public const LLAMA_3_8B = 'llama-3-8b';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $version = self::LLAMA_3_1_405B_INSTRUCT,
        private array $options = [],
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
        return false; // it does, but implementation here is still open.
    }

    public function supportsToolCalling(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }
}
