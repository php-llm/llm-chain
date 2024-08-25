<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\Document\Vector;
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

    public function create(string $text): Vector
    {
        $response = $this->runtime->request('embeddings', $this->createBody($text));

        return $this->extractVector($response);
    }

    public function multiCreate(array $texts): array
    {
        $bodies = array_map([$this, 'createBody'], $texts);

        $vectors = [];
        foreach ($this->runtime->multiRequest('embeddings', $bodies) as $response) {
            $vectors[] = $this->extractVector($response);
        }

        return $vectors;
    }

    private function createBody(string $text): array
    {
        return [
            'model' => $this->version->value,
            'input' => $text,
        ];
    }

    private function extractVector(array $data): Vector
    {
        return Vector::create1536($data['data'][0]['embedding']);
    }
}
