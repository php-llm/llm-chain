<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class Document extends File implements Content
{
    /**
     * @return array{type: 'document', source: array{type: 'base64', media_type: string, data: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'document',
            'source' => [
                'type' => 'base64',
                'media_type' => $this->getFormat(),
                'data' => $this->asBase64(),
            ],
        ];
    }
}
