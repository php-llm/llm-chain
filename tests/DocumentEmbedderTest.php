<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests;

use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\DocumentEmbedder;
use PhpLlm\LlmChain\Tests\Double\TestEmbeddingsModel;
use PhpLlm\LlmChain\Tests\Double\TestStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;

#[CoversClass(DocumentEmbedder::class)]
#[UsesClass(Document::class)]
#[UsesClass(Vector::class)]
final class DocumentEmbedderTest extends TestCase
{
    #[Test]
    public function embedSingleDocument(): void
    {
        $vectorData = [0.1, 0.2, 0.3];
        $vector = new Vector($vectorData);
        $document = new Document(Uuid::v4(), 'Test content', vector: null);

        $embedder = new DocumentEmbedder(
            new TestEmbeddingsModel(multiCreate: [$vector]),
            $store = new TestStore(),
            new MockClock(),
            new NullLogger(),
        );

        $embedder->embed($document);

        self::assertCount(1, $store->documents);
        self::assertInstanceOf(Document::class, $store->documents[0]);
        self::assertSame('Test content', $store->documents[0]->text);
        self::assertSame($vectorData, $store->documents[0]->vector->getData());
    }

    #[Test]
    public function embedEmptyDocumentList(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug')->with('No documents to embed');

        $embedder = new DocumentEmbedder(
            new TestEmbeddingsModel(),
            $store = new TestStore(),
            new MockClock(),
            $logger,
        );

        $embedder->embed([]);

        self::assertSame([], $store->documents);
    }

    #[Test]
    public function embedDocumentWithMetadata(): void
    {
        $vectorData = [0.1, 0.2, 0.3];
        $metadata = new Metadata(['key' => 'value']);
        $document = new Document(Uuid::v4(), 'Test content', null, $metadata);

        $embedder = new DocumentEmbedder(
            new TestEmbeddingsModel(multiCreate: [$vector = new Vector($vectorData)]),
            $store = new TestStore(),
            new MockClock(),
            new NullLogger(),
        );

        $embedder->embed($document);

        self::assertSame(1, $store->addDocumentsCalls);
        self::assertCount(1, $store->documents);
        self::assertInstanceOf(Document::class, $store->documents[0]);
        self::assertSame('Test content', $store->documents[0]->text);
        self::assertSame($vectorData, $store->documents[0]->vector->getData());
        self::assertSame(['key' => 'value'], $store->documents[0]->metadata->getArrayCopy());
    }

    #[Test]
    public function embedWithSleep(): void
    {
        $vectorData = [0.1, 0.2, 0.3];
        $document1 = new Document(Uuid::v4(), 'Test content 1', $vector1 = new Vector($vectorData));
        $document2 = new Document(Uuid::v4(), 'Test content 2', $vector2 = new Vector($vectorData));

        $embedder = new DocumentEmbedder(
            new TestEmbeddingsModel(multiCreate: [$vector1, $vector2]),
            $store = new TestStore(),
            $clock = new MockClock('2024-01-01 00:00:00'),
            new NullLogger(),
        );

        $embedder->embed(
            documents: [$document1, $document2],
            sleep: 3
        );

        self::assertSame(1, $store->addDocumentsCalls);
        self::assertCount(2, $store->documents);
        self::assertSame('2024-01-01 00:00:03', $clock->now()->format('Y-m-d H:i:s'));
    }
}
