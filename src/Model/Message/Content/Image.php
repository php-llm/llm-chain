<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;

final readonly class Image implements Content
{
    public string $url;

    /**
     * @param string $url An URL like "http://localhost:3000/my-image.png", a data url like "data:image/png;base64,iVBOR[...]"
     *                    or a file path like "/path/to/my-image.png".
     */
    public function __construct(string $url)
    {
        if (!str_starts_with($url, 'http') && !str_starts_with($url, 'data:')) {
            $url = $this->fromFile($url);
        }

        $this->url = $url;
    }

    /**
     * @return array{type: 'image_url', image_url: array{url: string}}
     */
    public function jsonSerialize(): array
    {
        return ['type' => 'image_url', 'image_url' => ['url' => $this->url]];
    }

    private function fromFile(string $filePath): string
    {
        if (!is_readable($filePath) || false === $data = file_get_contents($filePath)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable.', $filePath));
        }

        $type = pathinfo($filePath, PATHINFO_EXTENSION);

        return sprintf('data:image/%s;base64,%s', $type, base64_encode($data));
    }

    public function accept(ContentVisitor $visitor): array
    {
        return $visitor->visitImage($this);
    }
}
