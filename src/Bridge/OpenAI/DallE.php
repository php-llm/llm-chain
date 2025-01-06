<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\Model;

final readonly class DallE implements Model
{
    public const DALL_E_2 = 'dall-e-2';
    public const DALL_E_3 = 'dall-e-3';

    /** @param array<string, mixed> $options The default options for the model usage */
    public function __construct(
        private string $version = self::DALL_E_2,
        private array $options = [],
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }
}
