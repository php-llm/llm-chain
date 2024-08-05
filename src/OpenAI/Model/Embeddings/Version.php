<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model\Embeddings;

enum Version: string
{
    case EMBEDDING_ADA_002 = 'text-embedding-ada-002';
    case EMBEDDING_3_LARGE = 'text-embedding-3-large';
    case EMBEDDING_3_SMALL = 'text-embedding-3-small';
}
