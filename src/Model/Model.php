<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model;

class Model
{
    /**
     * @param string[]             $capabilities
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly string $name,
        private readonly array $capabilities = [],
        private readonly array $options = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
