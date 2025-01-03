<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\DallE;

use Webmozart\Assert\Assert;

final readonly class Base64Image
{
    public function __construct(
        public string $encodedImage,
    ) {
        Assert::stringNotEmpty($encodedImage, 'The base64 encoded image generated must be given.');
    }
}
