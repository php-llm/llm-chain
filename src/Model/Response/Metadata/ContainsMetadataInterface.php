<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response\Metadata;

interface ContainsMetadataInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    public function setMetadata(array $metadata): void;

    public function addMetadata(string $key, mixed $value): void;
}
