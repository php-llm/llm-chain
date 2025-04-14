<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Voyage;

use PhpLlm\LlmChain\Model\EmbeddingsModel;

final readonly class Voyage implements EmbeddingsModel
{
    public const V3 = 'voyage-3';
    public const V3_LITE = 'voyage-3-lite';
    public const FINANCE_2 = 'voyage-finance-2';
    public const MULTILINGUAL_2 = 'voyage-multilingual-2';
    public const LAW_2 = 'voyage-law-2';
    public const CODE_2 = 'voyage-code-2';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $name = self::V3,
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
