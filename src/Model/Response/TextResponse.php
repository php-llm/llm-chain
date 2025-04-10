<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Model\Response\Metadata\ContainsMetadataInterface;
use PhpLlm\LlmChain\Model\Response\Metadata\MetadataTrait;

final class TextResponse implements ResponseInterface, ContainsMetadataInterface
{
    use MetadataTrait;

    public function __construct(
        private readonly string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
