<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message\Content;

final readonly class DocumentUrl implements ContentInterface
{
    public function __construct(
        public string $url,
    ) {
    }
}
