<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message\Content;

final readonly class Image implements ContentInterface
{
    /**
     * @param string $url An URL like "http://localhost:3000/my-image.png" or a data url like "data:image/png;base64,iVBOR[...]"
     */
    public function __construct(
        public string $url,
    ) {
    }

    /**
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function jsonSerialize(): array
    {
        return ['type' => 'image_url', 'image_url' => ['url' => $this->url]];
    }
}
