<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Document;

use Symfony\Component\Uid\Uuid;

final readonly class VectorDocument
{
    public function __construct(
        public Uuid $id,
        public VectorInterface $vector,
        public Metadata $metadata = new Metadata(),
        public ?float $score = null,
    ) {
    }
}
