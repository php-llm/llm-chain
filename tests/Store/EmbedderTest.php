<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Store;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Platform;
use PhpLlm\LlmChain\Platform\Response\AsyncResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\Embedder;
use PhpLlm\LlmChain\Tests\Double\PlatformTestHandler;
use PhpLlm\LlmChain\Tests\Double\TestStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Embedder::class)]
#[Medium]
#[UsesClass(TextDocument::class)]
#[UsesClass(Vector::class)]
#[UsesClass(VectorDocument::class)]
#[UsesClass(ToolCallMessage::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(Embeddings::class)]
#[UsesClass(Platform::class)]
#[UsesClass(AsyncResponse::class)]
#[UsesClass(VectorResponse::class)]
final class EmbedderTest extends TestCase
{
    #[Test]
    public function embedSingleDocument(): void
    {
        $document = new TextDocument($id = Uuid::v4(), 'Test content');
        $vector = new Vector([0.1, 0.2, 0.3]);

        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(new VectorResponse($vector)),
            new Embeddings(),
            $store = new TestStore(),
            new MockClock(),
        );

        $embedder->embed($document);

        self::assertCount(1, $store->documents);
        self::assertInstanceOf(VectorDocument::class, $store->documents[0]);
        self::assertSame($id, $store->documents[0]->id);
        self::assertSame($vector, $store->documents[0]->vector);
    }

    #[Test]
    public function embedEmptyDocumentList(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with('No documents to embed');

        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(),
            new Embeddings(),
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
        $metadata = new Metadata(['key' => 'value']);
        $document = new TextDocument($id = Uuid::v4(), 'Test content', $metadata);
        $vector = new Vector([0.1, 0.2, 0.3]);

        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(new VectorResponse($vector)),
            new Embeddings(),
            $store = new TestStore(),
            new MockClock(),
        );

        $embedder->embed($document);

        self::assertSame(1, $store->addCalls);
        self::assertCount(1, $store->documents);
        self::assertInstanceOf(VectorDocument::class, $store->documents[0]);
        self::assertSame($id, $store->documents[0]->id);
        self::assertSame($vector, $store->documents[0]->vector);
        self::assertSame(['key' => 'value'], $store->documents[0]->metadata->getArrayCopy());
    }

    #[Test]
    public function embedWithSleep(): void
    {
        $vector1 = new Vector([0.1, 0.2, 0.3]);
        $vector2 = new Vector([0.4, 0.5, 0.6]);

        $document1 = new TextDocument(Uuid::v4(), 'Test content 1');
        $document2 = new TextDocument(Uuid::v4(), 'Test content 2');

        $embedder = new Embedder(
            PlatformTestHandler::createPlatform(new VectorResponse($vector1, $vector2)),
            new Embeddings(),
            $store = new TestStore(),
            $clock = new MockClock('2024-01-01 00:00:00'),
        );

        $embedder->embed(
            documents: [$document1, $document2],
            sleep: 3
        );

        self::assertSame(1, $store->addCalls);
        self::assertCount(2, $store->documents);
        self::assertSame('2024-01-01 00:00:03', $clock->now()->format('Y-m-d H:i:s'));
    }
}
