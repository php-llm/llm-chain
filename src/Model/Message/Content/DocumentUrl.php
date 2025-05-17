<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class DocumentUrl implements Content
{
    public function __construct(
        public string $url,
    ) {
    }

    /**
     * @return array{type: 'document', source: array{type: 'url', url: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'document',
            'source' => [
                'type' => 'url',
                'url' => $this->url,
            ],
        ];
    }
}
