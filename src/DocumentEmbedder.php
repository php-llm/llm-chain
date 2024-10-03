<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\TextDocument;
use PhpLlm\LlmChain\Document\VectorDocument;
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
     * @param TextDocument|TextDocument[] $documents
     */
    public function embed(TextDocument|array $documents, int $chunkSize = 0, int $sleep = 0): void
    {
        if ($documents instanceof TextDocument) {
            $documents = [$documents];
        }

        if ([] === $documents) {
            $this->logger->debug('No documents to embed');

            return;
        }

        $chunks = 0 !== $chunkSize ? array_chunk($documents, $chunkSize) : [$documents];

        foreach ($chunks as $chunk) {
            $vectors = $this->embeddings->multiCreate(array_map(fn (TextDocument $document) => $document->content, $chunk));

            $vectorDocuments = [];
            foreach ($chunk as $i => $document) {
                $vectorDocuments[] = new VectorDocument($document->id, $vectors[$i], $document->metadata);
            }

            $this->store->add(...$vectorDocuments);

            if (0 !== $sleep) {
                $this->clock->sleep($sleep);
            }
        }
    }
}
