<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final readonly class Document
{
    public function __construct(
        public Uuid $id,
        public string $text,
        public ?Vector $vector,
        public Metadata $metadata = new Metadata([]),
    ) {
        Assert::stringNotEmpty(trim($this->text));
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
