<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\Model;

class Embeddings extends Model
{
    public const TEXT_ADA_002 = 'text-embedding-ada-002';
    public const TEXT_3_LARGE = 'text-embedding-3-large';
    public const TEXT_3_SMALL = 'text-embedding-3-small';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $name = self::TEXT_3_SMALL, array $options = [])
    {
        parent::__construct($name, [], $options);
    }
}
