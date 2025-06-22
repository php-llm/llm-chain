<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Indexer
{
    private ClockInterface $clock;

    public function __construct(
        private PlatformInterface $platform,
        private Model $model,
        private StoreInterface $store,
        ?ClockInterface $clock = null,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        $this->clock = $clock ?? Clock::get();
    }

    /**
     * @param TextDocument|iterable<TextDocument> $documents
     */
    public function index(TextDocument|iterable $documents, int $chunkSize = 0, int $sleep = 0): void
    {
        if ($documents instanceof TextDocument) {
            $documents = [$documents];
        }

        if ([] === $documents) {
            $this->logger->debug('No documents to index');

            return;
        }

        $chunks = 0 !== $chunkSize ? array_chunk($documents, $chunkSize) : [$documents];

        foreach ($chunks as $chunk) {
            $this->store->add(...$this->createVectorDocuments($chunk));

            if (0 !== $sleep) {
                $this->clock->sleep($sleep);
            }
        }
    }

    /**
     * @param TextDocument[] $documents
     *
     * @return VectorDocument[]
     */
    private function createVectorDocuments(array $documents): array
    {
        if ($this->model->supports(Capability::INPUT_MULTIPLE)) {
            $response = $this->platform->request($this->model, array_map(fn (TextDocument $document) => $document->content, $documents));

            $vectors = $response->getContent();
        } else {
            $responses = [];
            foreach ($documents as $document) {
                $responses[] = $this->platform->request($this->model, $document->content);
            }

            $vectors = [];
            foreach ($responses as $response) {
                $vectors = array_merge($vectors, $response->getContent());
            }
        }

        $vectorDocuments = [];
        foreach ($documents as $i => $document) {
            $vectorDocuments[] = new VectorDocument($document->id, $vectors[$i], $document->metadata);
        }

        return $vectorDocuments;
    }
}
