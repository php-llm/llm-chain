<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use Webmozart\Assert\Assert;

final readonly class ImageUrl implements \JsonSerializable
{
    public function __construct(
        public string $url,
    ) {
        Assert::stringNotEmpty($url);
        Assert::startsWith($url, 'http');
    }

    /**
     * @return array{
     *     type: 'image_url',
     *     image_url: array{url: non-empty-string}
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'image_url',
            'image_url' => [
                'url' => $this->url,
            ],
        ];
    }
}
