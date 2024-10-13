<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Voyage;

use PhpLlm\LlmChain\Model\EmbeddingsModel;

final readonly class Voyage implements EmbeddingsModel
{
    public const VERSION_V3 = 'voyage-3';
    public const VERSION_V3_LITE = 'voyage-3-lite';
    public const VERSION_FINANCE_2 = 'voyage-finance-2';
    public const VERSION_MULTILINGUAL_2 = 'voyage-multilingual-2';
    public const VERSION_LAW_2 = 'voyage-law-2';
    public const VERSION_CODE_2 = 'voyage-code-2';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $version = self::VERSION_V3,
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
        return true;
    }
}
