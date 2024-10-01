<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Store\StoreInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class DocumentEmbedder
{
    public function __construct(
        private EmbeddingsModel $embeddings,
        private StoreInterface $store,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @param Document|list<Document> $documents
     */
    public function embed(Document|array $documents, int $chunkSize = 0, int $sleep = 0): void
    {
        if ($documents instanceof Document) {
            $documents = [$documents];
        }

        // Filter out documents without text
        $documents = array_filter($documents, fn (Document $document) => is_string($document->text));

        if ([] === $documents) {
            $this->logger->debug('No documents to embed');

            return;
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
