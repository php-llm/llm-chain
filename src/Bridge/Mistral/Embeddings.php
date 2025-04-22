<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Mistral;

use PhpLlm\LlmChain\Model\EmbeddingsModel;

final readonly class Embeddings implements EmbeddingsModel
{
    public const MISTRAL_EMBED = 'mistral-embed';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $name = self::MISTRAL_EMBED,
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

    public function supportsMultipleInputs(): bool
    {
        return true;
    }
}
