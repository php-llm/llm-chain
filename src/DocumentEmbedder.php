<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Store\StoreInterface;

final class DocumentEmbedder
{
    public function __construct(
        private EmbeddingModel $embeddings,
        private StoreInterface $store,
    ) {
    }

    public function embed(Document|array $documents, int $chunkSize = 0, int $sleep = 0): void
    {
        if ($documents instanceof Document) {
            $documents = [$documents];
        }

        $chunks = 0 !== $chunkSize ? array_chunk($documents, $chunkSize) : [$documents];

        foreach ($chunks as $chunk) {
            $vectors = $this->embeddings->multiCreate(array_map(fn (Document $document) => $document->text, $chunk));

            $vectorizedDocuments = [];
            foreach ($chunk as $i => $document) {
                $vectorizedDocuments[] = $document->withVector($vectors[$i]);
            }

            $this->store->addDocuments($vectorizedDocuments);

            if (0 !== $sleep) {
                sleep($sleep);
            }
        }
    }
}
