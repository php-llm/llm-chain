<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final readonly class TextResponse implements ResponseInterface
{
    public function __construct(
        private string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
