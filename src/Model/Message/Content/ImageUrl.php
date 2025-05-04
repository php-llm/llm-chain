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
     * @return array{type: 'image', source: array{type: 'url', url: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'image',
            'source' => [
                'type' => 'url',
                'url' => $this->url,
            ],
        ];
    }
}
