<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\AwsBedrock\Language;

use PhpLlm\LlmChain\Bridge\AwsBedrock\BedrockLanguageModel;

final readonly class AmazonNova implements BedrockLanguageModel
{
    public const NOVA_MICRO_V1 = 'amazon.nova-micro-v1:0';

    public const NOVA_LITE_V1 = 'amazon.nova-lite-v1:0';

    public const NOVA_PRO_V1 = 'amazon.nova-pro-v1:0';

    /**
     * @param array<string, mixed> $options
     * @param string|null          $inferenceProfileRegion if you need to use an inference profile just add the region here
     */
    public function __construct(
        private string $name = self::NOVA_LITE_V1,
        private array $options = [],
        private ?string $inferenceProfileRegion = null
    ) {
    }

    public function getName(): string
    {
        if ($this->inferenceProfileRegion) {
            return $this->inferenceProfileRegion.'.'.$this->name;
        }

        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function supportsAudioInput(): bool
    {
        return false; // it does, but implementation here is still open.
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
        return false; // it does, but implementation here is still open.
    }
}
