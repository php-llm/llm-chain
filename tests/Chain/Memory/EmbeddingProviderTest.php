<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Memory;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Memory\EmbeddingProvider;
use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Platform\Response\RawResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponsePromise;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(Text::class)]
#[UsesClass(ImageUrl::class)]
#[UsesClass(Message::class)]
#[UsesClass(Input::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(VectorStoreInterface::class)]
#[UsesClass(Model::class)]
#[UsesClass(PlatformInterface::class)]
#[CoversClass(EmbeddingProvider::class)]
#[Small]
final class EmbeddingProviderTest extends TestCase
{
    #[Test]
    public function itIsDoingNothingWithEmptyMessageBag(): void
    {
        $platform = $this->createMock(PlatformInterface::class);
        $platform->expects($this->never())->method('request');

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore->expects($this->never())->method('query');

        $embeddingProvider = new EmbeddingProvider(
            $platform,
            self::createStub(Model::class),
            $vectorStore,
        );

        $embeddingProvider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(),
            [],
        ));
    }

    #[Test]
    public function itIsDoingNothingWithoutUserMessageInBag(): void
    {
        $platform = $this->createMock(PlatformInterface::class);
        $platform->expects($this->never())->method('request');

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore->expects($this->never())->method('query');

        $embeddingProvider = new EmbeddingProvider(
            $platform,
            self::createStub(Model::class),
            $vectorStore,
        );

        $embeddingProvider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(Message::forSystem('This is a system message')),
            [],
        ));
    }

    #[Test]
    public function itIsDoingNothingWhenUserMessageHasNoTextContent(): void
    {
        $platform = $this->createMock(PlatformInterface::class);
        $platform->expects($this->never())->method('request');

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore->expects($this->never())->method('query');

        $embeddingProvider = new EmbeddingProvider(
            $platform,
            self::createStub(Model::class),
            $vectorStore,
        );

        $embeddingProvider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(Message::ofUser(new ImageUrl('foo.jpg'))),
            [],
        ));
    }

    #[Test]
    public function itIsNotCreatingMemoryWhenNoVectorsFound(): void
    {
        $vectorResponse = new VectorResponse($vector = new Vector([0.1, 0.2], 2));
        $responsePromise = new ResponsePromise(
            static fn () => $vectorResponse,
            self::createStub(RawResponseInterface::class),
        );

        $platform = $this->createMock(PlatformInterface::class);
        $platform->expects($this->once())
            ->method('request')
            ->willReturn($responsePromise);

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore->expects($this->once())
            ->method('query')
            ->with($vector)
            ->willReturn([]);

        $embeddingProvider = new EmbeddingProvider(
            $platform,
            self::createStub(Model::class),
            $vectorStore,
        );

        $memory = $embeddingProvider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(Message::ofUser(new Text('Have we talked about the weather?'))),
            [],
        ));

        self::assertCount(0, $memory);
    }

    #[Test]
    public function itIsCreatingMemoryWithFoundVectors(): void
    {
        $vectorResponse = new VectorResponse($vector = new Vector([0.1, 0.2], 2));
        $responsePromise = new ResponsePromise(
            static fn () => $vectorResponse,
            self::createStub(RawResponseInterface::class),
        );

        $platform = $this->createMock(PlatformInterface::class);
        $platform->expects($this->once())
            ->method('request')
            ->willReturn($responsePromise);

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore->expects($this->once())
            ->method('query')
            ->with($vector)
            ->willReturn([
                (object) ['metadata' => ['fact' => 'The sky is blue']],
                (object) ['metadata' => ['fact' => 'Water is wet']],
            ]);

        $embeddingProvider = new EmbeddingProvider(
            $platform,
            self::createStub(Model::class),
            $vectorStore,
        );

        $memory = $embeddingProvider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(Message::ofUser(new Text('Have we talked about the weather?'))),
            [],
        ));

        self::assertCount(1, $memory);
        self::assertSame(
            <<<MARKDOWN
                ## Dynamic memories fitting user message

                {"fact":"The sky is blue"}{"fact":"Water is wet"}
                MARKDOWN,
            $memory[0]->content,
        );
    }
}
