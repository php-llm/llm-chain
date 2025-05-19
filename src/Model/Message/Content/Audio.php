<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

final readonly class Audio extends File implements Content
{
    /**
     * @return array{type: 'input_audio', input_audio: array{data: string, format: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'input_audio',
            'input_audio' => [
                'data' => $this->asBase64(),
                'format' => match ($this->getFormat()) {
                    'audio/mpeg' => 'mp3',
                    'audio/wav' => 'wav',
                    default => $this->getFormat(),
                },
            ],
        ];
    }
}
