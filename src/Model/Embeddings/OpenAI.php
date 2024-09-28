<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Embeddings;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\Platform\OpenAI\Platform;

final readonly class OpenAI implements EmbeddingsModel
{
    public const TEXT_ADA_002 = 'text-embedding-ada-002';
    public const TEXT_3_LARGE = 'text-embedding-3-large';
    public const TEXT_3_SMALL = 'text-embedding-3-small';

    public function __construct(
        private Platform $platform,
        private string $version = self::TEXT_3_SMALL,
    ) {
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
            'model' => $this->version,
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
