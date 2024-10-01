<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class Document
{
    public function __construct(
        public Uuid $id,
        public ?string $text,
        public Metadata $metadata = new Metadata([]),
    ) {
    }

    public function hasText(): bool
    {
        return null !== $this->text;
    }

    public function withVector(Vector $vector): VectorizedDocument
    {
        return new VectorizedDocument(
            $this->id,
            $this->text,
            $vector,
            $this->metadata,
        );
    }
}
