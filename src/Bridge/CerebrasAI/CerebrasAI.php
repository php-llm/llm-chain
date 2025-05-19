<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\CerebrasAI;

use PhpLlm\LlmChain\Model\OpenAiCompatibleLanguageModel;

final readonly class CerebrasAI implements OpenAiCompatibleLanguageModel
{
    public const LLAMA_4_SCOUT_17B = 'llama-4-scout-17b-16e-instruct';
    public const LLAMA_31_8 = 'llama3.1-8b';
    public const LLAMA_33_70 = 'llama-3.3-70b';
    public const QWEN_3_32 = 'qwen-3-32b';
    public const DEEPSEEK_R1 = 'deepseek-r1-distill-llama-70b';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $name = self::LLAMA_31_8,
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
        return false;
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function supportsToolCalling(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return true;
    }
}
