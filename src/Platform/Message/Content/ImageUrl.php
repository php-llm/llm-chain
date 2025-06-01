<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message\Content;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ImageUrl implements ContentInterface
{
    public function __construct(
        public string $url,
    ) {
    }
}
