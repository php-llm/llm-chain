<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
