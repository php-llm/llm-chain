<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

readonly class Document
{
    public function __construct(
        public Uuid $id,
        public string $text,
        public Metadata $metadata = new Metadata([]),
    ) {
        Assert::stringNotEmpty(trim($this->text));
    }

    public function withVector(Vector $vector): EmbeddedDocument
    {
        return new EmbeddedDocument(
            $this->id,
            $this->text,
            $vector,
            $this->metadata,
        );
    }
}
