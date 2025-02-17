<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

final readonly class Audio implements Content
{
    public function __construct(
        public string $path,
    ) {
        if (!is_readable($path) || false === file_get_contents($path)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable.', $path));
        }
    }

    /**
     * @return array{type: 'input_audio', input_audio: array{data: string, format: string}}
     */
    public function jsonSerialize(): array
    {
        $data = file_get_contents($this->path);
        $format = pathinfo($this->path, PATHINFO_EXTENSION);

        return [
            'type' => 'input_audio',
            'input_audio' => [
                'data' => base64_encode($data),
                'format' => $format,
            ],
        ];
    }

    public function accept(ContentVisitor $visitor): array
    {
        return $visitor->visitAudio($this);
    }
}
