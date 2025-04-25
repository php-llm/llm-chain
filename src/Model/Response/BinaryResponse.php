<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final class BinaryResponse extends BaseResponse
{
    public function __construct(
        public string $data,
        public ?string $mimeType = null,
    ) {
    }

    public function getContent(): string
    {
        return $this->data;
    }

    public function toBase64(): string
    {
        return \base64_encode($this->data);
    }

    public function toDataUri(): string
    {
        if (null === $this->mimeType) {
            throw new \RuntimeException('Mime type is not set.');
        }

        return 'data:'.$this->mimeType.';base64,'.$this->toBase64();
    }
}
