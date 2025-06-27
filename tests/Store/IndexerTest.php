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
use PhpLlm\LlmChain\Store\Document\Vectorizer;
use PhpLlm\LlmChain\Store\Indexer;
use PhpLlm\LlmChain\Tests\Double\PlatformTestHandler;
use PhpLlm\LlmChain\Tests\Double\TestStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Indexer::class)]
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
final class IndexerTest extends TestCase
{
    #[Test]
    public function indexSingleDocument(): void
    {
        $document = new TextDocument($id = Uuid::v4(), 'Test content');
        $vector = new Vector([0.1, 0.2, 0.3]);
        $vectorizer = new Vectorizer(PlatformTestHandler::createPlatform(new VectorResponse($vector)), new Embeddings());

        $indexer = new Indexer($vectorizer, $store = new TestStore());
        $indexer->index($document);

        self::assertCount(1, $store->documents);
        self::assertInstanceOf(VectorDocument::class, $store->documents[0]);
        self::assertSame($id, $store->documents[0]->id);
        self::assertSame($vector, $store->documents[0]->vector);
    }

    #[Test]
    public function indexEmptyDocumentList(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with('No documents to index');
        $vectorizer = new Vectorizer(PlatformTestHandler::createPlatform(), new Embeddings());

        $indexer = new Indexer($vectorizer, $store = new TestStore(), $logger);
        $indexer->index([]);

        self::assertSame([], $store->documents);
    }

    #[Test]
    public function indexDocumentWithMetadata(): void
    {
        $metadata = new Metadata(['key' => 'value']);
        $document = new TextDocument($id = Uuid::v4(), 'Test content', $metadata);
        $vector = new Vector([0.1, 0.2, 0.3]);
        $vectorizer = new Vectorizer(PlatformTestHandler::createPlatform(new VectorResponse($vector)), new Embeddings());

        $indexer = new Indexer($vectorizer, $store = new TestStore());
        $indexer->index($document);

        self::assertSame(1, $store->addCalls);
        self::assertCount(1, $store->documents);
        self::assertInstanceOf(VectorDocument::class, $store->documents[0]);
        self::assertSame($id, $store->documents[0]->id);
        self::assertSame($vector, $store->documents[0]->vector);
        self::assertSame(['key' => 'value'], $store->documents[0]->metadata->getArrayCopy());
    }
}
