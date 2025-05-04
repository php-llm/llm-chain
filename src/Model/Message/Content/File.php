<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\RuntimeException;

use function Symfony\Component\String\u;

readonly class File implements Content
{
    final public function __construct(
        private string|\Closure $data,
        private string $format,
        private ?string $path = null,
    ) {
    }

    public static function fromDataUrl(string $dataUrl): static
    {
        if (!str_starts_with($dataUrl, 'data:')) {
            throw new InvalidArgumentException('Invalid audio data URL format.');
        }

        return new static(
            base64_decode(u($dataUrl)->after('base64,')->toString()),
            u($dataUrl)->after('data:')->before(';base64,')->toString(),
        );
    }

    public static function fromFile(string $path): static
    {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable.', $path));
        }

        return new static(
            fn () => file_get_contents($path),
            mime_content_type($path),
            $path,
        );
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function asBinary(): string
    {
        return $this->data instanceof \Closure ? ($this->data)() : $this->data;
    }

    public function asBase64(): string
    {
        return base64_encode($this->asBinary());
    }

    public function asDataUrl(): string
    {
        return sprintf('data:%s;base64,%s', $this->format, $this->asBase64());
    }

    /**
     * @return resource|false
     */
    public function asResource()
    {
        if (null === $this->path) {
            throw new RuntimeException('You can only get a resource after creating fromFile.');
        }

        return fopen($this->path, 'r');
    }
}
