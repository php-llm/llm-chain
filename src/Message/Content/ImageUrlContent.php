<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message\Content;

final readonly class ImageUrlContent implements ContentInterface
{
    public function __construct(public string $imageUrl)
    {
    }

    /**
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function jsonSerialize(): array
    {
        return ['type' => 'image_url', 'image_url' => ['url' => $this->imageUrl]];
    }
}
