<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class Image extends File implements Content
{
    /**
     * @return array{type: 'image', image_url: array{url: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $this->getFormat(),
                'data' => $this->asBase64(),
            ],
        ];
    }
}
