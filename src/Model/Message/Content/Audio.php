<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

use function Symfony\Component\String\u;

final readonly class Audio implements Content
{
    public function __construct(
        public string $data,
        public string $format,
    ) {
    }

    public static function fromDataUrl(string $dataUrl): self
    {
        if (!str_starts_with($dataUrl, 'data:audio/')) {
            throw new InvalidArgumentException('Invalid audio data URL format.');
        }

        return new self(
            u($dataUrl)->after('base64,')->toString(),
            u($dataUrl)->after('data:audio/')->before(';base64,')->toString(),
        );
    }

    public static function fromFile(string $filePath): self
    {
        if (!is_readable($filePath) || false === $audioData = file_get_contents($filePath)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable.', $filePath));
        }

        return new self(
            base64_encode($audioData),
            pathinfo($filePath, PATHINFO_EXTENSION)
        );
    }

    /**
     * @return array{type: 'input_audio', input_audio: array{data: string, format: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'input_audio',
            'input_audio' => [
                'data' => $this->data,
                'format' => $this->format,
            ],
        ];
    }
}
