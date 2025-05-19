<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAICompatible;

use PhpLlm\LlmChain\Model\OpenAiCompatibleLanguageModel;

final readonly class GenericModel implements OpenAiCompatibleLanguageModel
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $name,
        private array $options = [],
        private bool $supportsAudioInput = false,
        private bool $supportsImageInput = false,
        private bool $supportsToolCalling = false,
        private bool $supportsStructuredOutput = false,
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

    public function supportsToolCalling(): bool
    {
        return $this->supportsToolCalling;
    }

    public function supportsStructuredOutput(): bool
    {
        return $this->supportsStructuredOutput;
    }
}
