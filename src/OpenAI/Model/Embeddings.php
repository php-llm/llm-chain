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

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings\Version;
use PhpLlm\LlmChain\OpenAI\Platform;

final class Embeddings implements EmbeddingModel
{
    public function __construct(
        private readonly Platform $platform,
        private ?Version $version = null,
    ) {
        $this->version ??= Version::textEmbedding3Small();
    }

    public function create(string $text): Vector
    {
        $response = $this->platform->request('embeddings', $this->createBody($text));

        return $this->extractVector($response);
    }

    public function multiCreate(array $texts): array
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
        return Vector::create1536($data['data'][0]['embedding']);
    }
}
