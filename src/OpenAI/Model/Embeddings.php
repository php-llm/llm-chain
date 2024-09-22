<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings\Version;
use PhpLlm\LlmChain\OpenAI\Runtime;

final readonly class Embeddings implements EmbeddingModel
{
    public function __construct(
        private Runtime $runtime,
        private ?Model $model = null,
    ) {
        $this->model = $this->model ?? Model::fromVersion(Version::EMBEDDING_3_SMALL);
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

    /**
     * @return array{model: non-empty-string, input: string}
     */
    private function createBody(string $text): array
    {
        return [
            'model' => $this->model->getName(),
            'input' => $text,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractVector(array $data): Vector
    {
        return Vector::create1536($data['data'][0]['embedding']);
    }
}
