<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Vector\Pinecone;

use PhpLlm\LlmChain\OpenAI\Embeddings;
use PhpLlm\LlmChain\RetrieverInterface;

final class Retriever implements RetrieverInterface
{
    public function __construct(
        private Embeddings $embeddings,
        private Pinecone $client,
    ) {
    }

    /**
     * @param string $search text used for embedding and similarity search
     */
    public function enrich(string $search): string
    {
        $vector = $this->embeddings->create($search);
        $hits = $this->client->query($vector);

        $result = '';
        foreach ($hits as $hit) {
            foreach ($hit['metadata'] as $key => $value) {
                $result .= ucfirst($key).': '.$value.', ';
            }
            $result .= PHP_EOL;
        }

        return $result;
    }
}
