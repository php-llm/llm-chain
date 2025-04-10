<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response\Metadata;

trait MetadataTrait
{
    /**
     * @var array<string, mixed>
     */
    private array $metadata = [];

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
