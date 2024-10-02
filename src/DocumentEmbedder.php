<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Store\StoreInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;

final readonly class DocumentEmbedder
{
    private ClockInterface $clock;

    public function __construct(
        private EmbeddingsModel $embeddings,
        private StoreInterface $store,
        ?ClockInterface $clock = null,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        $this->clock = $clock ?? Clock::get();
    }

    /**
     * @param Document|list<Document> $documents
     */
    public function embed(Document|array $documents, int $chunkSize = 0, int $sleep = 0): void
    {
        if ($documents instanceof Document) {
            $documents = [$documents];
        }

        if ([] === $documents) {
            $this->logger->debug('No documents to embed');

            return;
        }

        $chunks = 0 !== $chunkSize ? array_chunk($documents, $chunkSize) : [$documents];

        foreach ($chunks as $chunk) {
            $vectors = $this->embeddings->multiCreate(array_map(fn (Document $document) => $document->text, $chunk));

            $embeddedDocuments = [];
            foreach ($chunk as $i => $document) {
                $embeddedDocuments[] = $document->withVector($vectors[$i]);
            }

            $this->store->addDocuments(...$embeddedDocuments);

            if (0 !== $sleep) {
                $this->clock->sleep($sleep);
            }
        }
    }
}
