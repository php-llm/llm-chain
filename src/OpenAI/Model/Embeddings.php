<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings\Version;
use PhpLlm\LlmChain\OpenAI\Runtime;

final class Embeddings implements EmbeddingModel
{
    public function __construct(
        private Runtime $runtime,
        private Version $version = Version::EMBEDDING_3_SMALL,
    ) {
    }

    public function create(string $text): array
    {
        $body = [
            'model' => $this->version->value,
            'input' => $text,
        ];

        $response = $this->runtime->request('embeddings', $body);

        return $response['data'][0]['embedding'];
    }
}
