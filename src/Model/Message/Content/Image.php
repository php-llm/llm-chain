<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class Image extends File implements Content
{
    /**
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'image_url',
            'image_url' => ['url' => $this->asDataUrl()],
        ];
    }
}
