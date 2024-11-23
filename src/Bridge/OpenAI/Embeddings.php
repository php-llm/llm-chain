<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\EmbeddingsModel;

final readonly class Embeddings implements EmbeddingsModel
{
    public const TEXT_ADA_002 = 'text-embedding-ada-002';
    public const TEXT_3_LARGE = 'text-embedding-3-large';
    public const TEXT_3_SMALL = 'text-embedding-3-small';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $version = self::TEXT_3_SMALL,
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

    public function supportsMultipleInputs(): bool
    {
        return false;
    }
}
