<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message\Content;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class Text implements ContentInterface
{
    public function __construct(
        public string $text,
    ) {
    }
}
