<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\OpenAI\Model\Embeddings;

use Webmozart\Assert\Assert;

final readonly class Version
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
