<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message\Content;

final readonly class Text implements ContentInterface
{
    public function __construct(
        public string $text,
    ) {
    }

    /**
     * @return array{type: 'text', text: string}
     */
    public function jsonSerialize(): array
    {
        return ['type' => 'text', 'text' => $this->text];
    }
}
