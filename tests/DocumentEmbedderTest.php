<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\DocumentEmbedder;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\Store\StoreInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;

#[CoversClass(DocumentEmbedder::class)]
#[Small]
final class DocumentEmbedderTest extends TestCase
{
    private EmbeddingsModel&MockObject $embeddings;
    private StoreInterface&MockObject $store;
    private MockClock $clock;
    private LoggerInterface&MockObject $logger;
    private DocumentEmbedder $embedder;

    protected function setUp(): void
    {
        $this->embeddings = $this->createMock(EmbeddingsModel::class);
        $this->store = $this->createMock(StoreInterface::class);
        $this->clock = new MockClock('2024-01-01 00:00:00');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->embedder = new DocumentEmbedder(
            $this->embeddings,
            $this->store,
            $this->clock,
            $this->logger,
        );
    }

    #[Test]
    public function embedSingleDocument(): void
    {
        $vectorData = [0.1, 0.2, 0.3];
        $document = new Document(Uuid::v4(), 'Test content', $vector = new Vector($vectorData));

        $this->embeddings->method('multiCreate')->willReturn([$vector]);
        $this->store->expects($this->once())->method('addDocuments')->with($this->callback(function ($docs) use ($vector) {
            self::assertSame($vector->getData(), $docs[0]->vector->getData());
            self::assertSame('Test content', $docs[0]->text);

            return true;
        }));

        $this->embedder->embed($document);
    }

    #[Test]
    public function embedEmptyDocumentList(): void
    {
        $this->logger->expects($this->once())->method('debug')->with('No documents to embed');
        $this->store->expects($this->never())->method('addDocuments');

        $this->embedder->embed([]);
    }

    #[Test]
    public function embedDocumentWithoutText(): void
    {
        $document = new Document(Uuid::v4(), null, null);

        $this->logger->expects($this->once())->method('debug')->with('No documents to embed');
        $this->embeddings->expects($this->never())->method('multiCreate');
        $this->store->expects($this->never())->method('addDocuments');

        $this->embedder->embed($document);
    }

    #[Test]
    public function embedDocumentWithMetadata(): void
    {
        $vectorData = [0.1, 0.2, 0.3];
        $metadata = new Metadata(['key' => 'value']);
        $document = Document::fromText('Test content', Uuid::v4(), $metadata);

        $this->embeddings->method('multiCreate')->willReturn([$vector = new Vector($vectorData)]);
        $this->store->expects($this->once())->method('addDocuments')->with($this->callback(function ($docs) use ($vector, $metadata) {
            self::assertSame($vector->getData(), $docs[0]->vector->getData());
            self::assertSame('Test content', $docs[0]->text);
            self::assertSame([
                'key' => 'value',
            ], $metadata->getArrayCopy());

            return true;
        }));

        $this->embedder->embed($document);
    }

    #[Test]
    public function embedWithSleep(): void
    {
        $vectorData = [0.1, 0.2, 0.3];
        $document1 = new Document(Uuid::v4(), 'Test content 1', $vector1 = new Vector($vectorData));
        $document2 = new Document(Uuid::v4(), 'Test content 2', $vector2 = new Vector($vectorData));

        $this->embeddings->method('multiCreate')->willReturn([$vector1, $vector2]);

        $this->store->expects($this->once())->method('addDocuments')->with([$document1, $document2]);

        $this->embedder->embed(
            documents: [$document1, $document2],
            sleep: 3
        );

        self::assertSame('2024-01-01 00:00:03', $this->clock->now()->format('Y-m-d H:i:s'));
    }
}
