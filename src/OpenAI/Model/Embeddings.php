<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings\Version;
use PhpLlm\LlmChain\OpenAI\Platform;

final class Embeddings implements EmbeddingsModel
{
    public function __construct(
        private readonly Platform $platform,
        private ?Version $version = null,
    ) {
        $this->version ??= Version::textEmbedding3Small();
    }

    public function create(string $text, array $options = []): Vector
    {
        $response = $this->platform->request('embeddings', $this->createBody($text));

        return $this->extractVector($response);
    }

    public function multiCreate(array $texts, array $options = []): array
    {
        $bodies = array_map([$this, 'createBody'], $texts);

        $vectors = [];
        foreach ($this->platform->multiRequest('embeddings', $bodies) as $response) {
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
            'model' => $this->version->name,
            'input' => $text,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractVector(array $data): Vector
    {
        return new Vector($data['data'][0]['embedding']);
    }
}
