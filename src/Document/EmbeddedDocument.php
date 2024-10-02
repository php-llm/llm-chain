<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;

final readonly class EmbeddedDocument extends Document
{
    public function __construct(
        Uuid $id,
        string $text,
        public Vector $vector,
        Metadata $metadata = new Metadata([]),
    ) {
        parent::__construct($id, $text, $metadata);
    }

}
