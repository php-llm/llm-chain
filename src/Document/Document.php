<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

final readonly class Document
{
    public function __construct(
        public Uuid $id,
        public ?string $text,
        public ?Vector $vector,
        public Metadata $metadata = new Metadata([]),
    ) {
    }

    public static function fromText(string $text, Uuid $id = new UuidV4(), Metadata $metadata = new Metadata([])): self
    {
        return new self(
            $id,
            $text,
            null,
            $metadata,
        );
    }

    public static function fromVector(Vector $vector, Uuid $id = new UuidV4(), Metadata $metadata = new Metadata([])): self
    {
        return new self(
            $id,
            null,
            $vector,
            $metadata,
        );
    }

    public function withText(string $text): self
    {
        return new self(
            $this->id,
            $text,
            $this->vector,
            $this->metadata,
        );
    }

    public function hasText(): bool
    {
        return null !== $this->text;
    }

    public function withVector(Vector $vector): self
    {
        return new self(
            $this->id,
            $this->text,
            $vector,
            $this->metadata,
        );
    }

    public function hasVector(): bool
    {
        return null !== $this->vector;
    }
}
