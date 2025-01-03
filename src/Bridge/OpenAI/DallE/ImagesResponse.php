<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\DallE;

use PhpLlm\LlmChain\Model\Response\ResponseInterface;

class ImagesResponse implements ResponseInterface
{
    /** @var list<Base64Image|UrlImage> */
    private readonly array $images;

    public function __construct(
        public ?string $revisedPrompt = null, // Only string on Dall-E 3 usage
        Base64Image|UrlImage ...$images,
    ) {
        $this->images = \array_values($images);
    }

    /**
     * @return list<Base64Image|UrlImage>
     */
    public function getContent(): array
    {
        return $this->images;
    }
}
