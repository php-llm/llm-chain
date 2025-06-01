<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE;

use Webmozart\Assert\Assert;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class UrlImage
{
    public function __construct(
        public string $url,
    ) {
        Assert::stringNotEmpty($url, 'The image url must be given.');
    }
}
