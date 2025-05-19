<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final class TextResponse extends BaseResponse
{
    public function __construct(
        private readonly string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
