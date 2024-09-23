<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model\Embeddings;

use Webmozart\Assert\Assert;

final class Version
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
    ) {
        Assert::stringNotEmpty($name);
    }

    public static function textEmbeddingAda002(): self
    {
        return new self('text-embedding-ada-002');
    }

    public static function textEmbedding3Large(): self
    {
        return new self('text-embedding-3-large');
    }

    public static function textEmbedding3Small(): self
    {
        return new self('text-embedding-3-small');
    }
}
