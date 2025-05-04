<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class Text implements Content
{
    public function __construct(
        public string $text,
    ) {
    }
}
