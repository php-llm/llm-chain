<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Store\Document\Transformer;

use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\Transformer\ChainTransformer;
use PhpLlm\LlmChain\Store\Document\TransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(TransformerInterface::class)]
final class ChainTransformerTest extends TestCase
{
    #[Test]
    public function chainTransformerAppliesAllTransformersInOrder(): void
    {
        $transformerA = new class implements TransformerInterface {
            public function __invoke(iterable $documents, array $options = []): iterable
            {
                foreach ($documents as $document) {
                    yield new TextDocument($document->id, $document->content.'-A');
                }
            }
        };

        $transformerB = new class implements TransformerInterface {
            public function __invoke(iterable $documents, array $options = []): iterable
            {
                foreach ($documents as $document) {
                    yield new TextDocument($document->id, $document->content.'-B');
                }
            }
        };

        $chain = new ChainTransformer([$transformerA, $transformerB]);
        $documents = [
            new TextDocument(Uuid::v4(), 'foo'),
            new TextDocument(Uuid::v4(), 'bar'),
        ];

        $result = iterator_to_array($chain->__invoke($documents));

        self::assertSame('foo-A-B', $result[0]->content);
        self::assertSame('bar-A-B', $result[1]->content);
    }

    public function testChainTransformerWithNoTransformersReturnsInput(): void
    {
        $chain = new ChainTransformer([]);
        $documents = [new TextDocument(Uuid::v4(), 'baz')];

        $result = iterator_to_array($chain->__invoke($documents));

        self::assertSame('baz', $result[0]->content);
    }
}
