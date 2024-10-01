<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

final class VectorizedDocument extends Document
{
    public function __construct(
        public Uuid $id,
        public ?string $text,
        public ?Vector $vector,
        public Metadata $metadata = new Metadata([]),
    ) {
        parent::__construct($id, $text, $metadata);
    }

    public function hasVector(): bool
    {
        return $this->vector instanceof Vector;
    }
}
