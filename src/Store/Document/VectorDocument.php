<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Document;

use PhpLlm\LlmChain\Platform\Vector\VectorInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
