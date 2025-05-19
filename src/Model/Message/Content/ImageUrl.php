<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class ImageUrl implements Content
{
    public function __construct(
        public string $url,
    ) {
    }

    /**
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'image_url',
            'image_url' => ['url' => $this->url],
        ];
    }
}
