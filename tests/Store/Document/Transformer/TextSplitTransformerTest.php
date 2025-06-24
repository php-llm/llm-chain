<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Store\Document\Transformer;

use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\Transformer\TextSplitTransformer;
use PhpLlm\LlmChain\Store\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(TextSplitTransformer::class)]
final class TextSplitTransformerTest extends TestCase
{
    private TextSplitTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new TextSplitTransformer();
    }

    #[Test]
    public function splitReturnsSingleChunkForShortText(): void
    {
        $document = new TextDocument(Uuid::v4(), 'short text');

        $chunks = iterator_to_array(($this->transformer)([$document]));

        self::assertCount(1, $chunks);
        self::assertSame('short text', $chunks[0]->content);
    }

    #[Test]
    public function textLength(): void
    {
        self::assertSame(1500, mb_strlen($this->getLongText()));
    }

    #[Test]
    public function splitSplitsLongTextWithOverlap(): void
    {
        $document = new TextDocument(Uuid::v4(), $this->getLongText());

        $chunks = iterator_to_array(($this->transformer)([$document]));

        self::assertCount(2, $chunks);

        self::assertSame(1000, mb_strlen($chunks[0]->content));
        self::assertSame(substr($this->getLongText(), 0, 1000), $chunks[0]->content);

        self::assertSame(700, mb_strlen($chunks[1]->content));
        self::assertSame(substr($this->getLongText(), 800, 700), $chunks[1]->content);
    }

    #[Test]
    public function splitWithCustomChunkSizeAndOverlap(): void
    {
        $document = new TextDocument(Uuid::v4(), $this->getLongText());

        $chunks = iterator_to_array(($this->transformer)([$document], [
            TextSplitTransformer::OPTION_CHUNK_SIZE => 150,
            TextSplitTransformer::OPTION_OVERLAP => 25,
        ]));

        self::assertCount(12, $chunks);

        self::assertSame(150, mb_strlen($chunks[0]->content));
        self::assertSame(substr($this->getLongText(), 0, 150), $chunks[0]->content);

        self::assertSame(150, mb_strlen($chunks[1]->content));
        self::assertSame(substr($this->getLongText(), 125, 150), $chunks[1]->content);

        self::assertSame(150, mb_strlen($chunks[2]->content));
        self::assertSame(substr($this->getLongText(), 250, 150), $chunks[2]->content);

        self::assertSame(150, mb_strlen($chunks[3]->content));
        self::assertSame(substr($this->getLongText(), 375, 150), $chunks[3]->content);

        self::assertSame(150, mb_strlen($chunks[4]->content));
        self::assertSame(substr($this->getLongText(), 500, 150), $chunks[4]->content);

        self::assertSame(150, mb_strlen($chunks[5]->content));
        self::assertSame(substr($this->getLongText(), 625, 150), $chunks[5]->content);

        self::assertSame(150, mb_strlen($chunks[6]->content));
        self::assertSame(substr($this->getLongText(), 750, 150), $chunks[6]->content);

        self::assertSame(150, mb_strlen($chunks[7]->content));
        self::assertSame(substr($this->getLongText(), 875, 150), $chunks[7]->content);

        self::assertSame(150, mb_strlen($chunks[8]->content));
        self::assertSame(substr($this->getLongText(), 1000, 150), $chunks[8]->content);

        self::assertSame(150, mb_strlen($chunks[9]->content));
        self::assertSame(substr($this->getLongText(), 1125, 150), $chunks[9]->content);

        self::assertSame(150, mb_strlen($chunks[10]->content));
        self::assertSame(substr($this->getLongText(), 1250, 150), $chunks[10]->content);

        self::assertSame(125, mb_strlen($chunks[11]->content));
        self::assertSame(substr($this->getLongText(), 1375, 150), $chunks[11]->content);
    }

    #[Test]
    public function splitWithZeroOverlap(): void
    {
        $document = new TextDocument(Uuid::v4(), $this->getLongText());

        $chunks = iterator_to_array(($this->transformer)([$document], [
            TextSplitTransformer::OPTION_OVERLAP => 0,
        ]));

        self::assertCount(2, $chunks);
        self::assertSame(substr($this->getLongText(), 0, 1000), $chunks[0]->content);
        self::assertSame(substr($this->getLongText(), 1000, 500), $chunks[1]->content);
    }

    #[Test]
    public function parentIdIsSetInMetadata(): void
    {
        $document = new TextDocument(Uuid::v4(), $this->getLongText());

        $chunks = iterator_to_array(($this->transformer)([$document], [
            TextSplitTransformer::OPTION_CHUNK_SIZE => 1000,
            TextSplitTransformer::OPTION_OVERLAP => 200,
        ]));

        self::assertCount(2, $chunks);
        self::assertSame($document->id, $chunks[0]->metadata['parent_id']);
        self::assertSame($document->id, $chunks[1]->metadata['parent_id']);
    }

    #[Test]
    public function metadataIsInherited(): void
    {
        $document = new TextDocument(Uuid::v4(), $this->getLongText(), new Metadata([
            'key' => 'value',
            'foo' => 'bar',
        ]));

        $chunks = iterator_to_array(($this->transformer)([$document]));

        self::assertCount(2, $chunks);
        self::assertSame('value', $chunks[0]->metadata['key']);
        self::assertSame('bar', $chunks[0]->metadata['foo']);
        self::assertSame('value', $chunks[1]->metadata['key']);
        self::assertSame('bar', $chunks[1]->metadata['foo']);
    }

    #[Test]
    public function splitWithChunkSizeLargerThanText(): void
    {
        $document = new TextDocument(Uuid::v4(), 'tiny');

        $chunks = iterator_to_array(($this->transformer)([$document]));

        self::assertCount(1, $chunks);
        self::assertSame('tiny', $chunks[0]->content);
    }

    #[Test]
    public function splitWithOverlapGreaterThanChunkSize(): void
    {
        $document = new TextDocument(Uuid::v4(), 'Abcdefg', new Metadata([]));
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Overlap must be non-negative and less than chunk size.');

        iterator_to_array(($this->transformer)([$document], [
            TextSplitTransformer::OPTION_CHUNK_SIZE => 10,
            TextSplitTransformer::OPTION_OVERLAP => 20,
        ]));
    }

    #[Test]
    public function splitWithNegativeOverlap(): void
    {
        $document = new TextDocument(Uuid::v4(), 'Abcdefg', new Metadata([]));
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Overlap must be non-negative and less than chunk size.');

        iterator_to_array(($this->transformer)([$document], [
            TextSplitTransformer::OPTION_CHUNK_SIZE => 10,
            TextSplitTransformer::OPTION_OVERLAP => -1,
        ]));
    }

    private function getLongText(): string
    {
        return trim(file_get_contents(\dirname(__DIR__, 3).'/Fixture/lorem.txt'));
    }
}
