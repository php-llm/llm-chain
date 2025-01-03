<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\DallE;

use Webmozart\Assert\Assert;

final readonly class UrlImage
{
    public function __construct(
        public string $url,
    ) {
        Assert::stringNotEmpty($url, 'The image url must be given.');
    }
}
