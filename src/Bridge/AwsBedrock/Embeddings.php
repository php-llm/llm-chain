<?php

namespace PhpLlm\LlmChain\Bridge\AwsBedrock;

use PhpLlm\LlmChain\Model\EmbeddingsModel;

final readonly class Embeddings implements EmbeddingsModel
{
    public const TITAN_EMBED_TEXT_V2 = 'amazon.titan-embed-text-v2:0';
    public const TITAN_MULTIMODAL_G1 = 'amazon.titan-embed-image-v1';

    public function __construct(
        private string $name = self::TITAN_EMBED_TEXT_V2,
        private array $options = [],
        private ?string $inferenceProfileRegion = null,
    ) {
    }

    public function supportsMultipleInputs(): bool
    {
        return false;
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
}
