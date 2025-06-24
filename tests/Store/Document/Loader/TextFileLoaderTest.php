<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Store\Document\Loader;

use PhpLlm\LlmChain\Store\Document\Loader\TextFileLoader;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextFileLoader::class)]
final class TextFileLoaderTest extends TestCase
{
    #[Test]
    public function loadWithInvalidSource(): void
    {
        $loader = new TextFileLoader();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('File "/invalid/source.txt" does not exist.');

        iterator_to_array($loader('/invalid/source.txt'));
    }

    #[Test]
    public function loadWithValidSource(): void
    {
        $loader = new TextFileLoader();

        $documents = iterator_to_array($loader(\dirname(__DIR__, 3).'/Fixture/lorem.txt'));

        self::assertCount(1, $documents);
        self::assertInstanceOf(TextDocument::class, $document = $documents[0]);
        self::assertStringStartsWith('Lorem ipsum', $document->content);
        self::assertStringEndsWith('nonummy id, met', $document->content);
        self::assertSame(1500, \strlen($document->content));
    }

    #[Test]
    public function sourceIsPresentInMetadata(): void
    {
        $loader = new TextFileLoader();

        $source = \dirname(__DIR__, 3).'/Fixture/lorem.txt';
        $documents = iterator_to_array($loader($source));

        self::assertCount(1, $documents);
        self::assertInstanceOf(TextDocument::class, $document = $documents[0]);
        self::assertSame($source, $document->metadata['source']);
    }
}
