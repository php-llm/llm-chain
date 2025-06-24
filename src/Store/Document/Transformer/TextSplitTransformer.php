<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Document\Transformer;

use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\TransformerInterface;
use PhpLlm\LlmChain\Store\Exception\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

/**
 * Splits a TextDocument into smaller chunks of specified size with optional overlap.
 * If the document's content is shorter than the specified chunk size, it returns the original document as a single chunk.
 * Overlap cannot be negative and must be less than the chunk size.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class TextSplitTransformer implements TransformerInterface
{
    public const OPTION_CHUNK_SIZE = 'chunk_size';
    public const OPTION_OVERLAP = 'overlap';

    /**
     * @param array{chunk_size?: int, overlap?: int} $options
     */
    public function __invoke(iterable $documents, array $options = []): iterable
    {
        $chunkSize = $options[self::OPTION_CHUNK_SIZE] ?? 1000;
        $overlap = $options[self::OPTION_OVERLAP] ?? 200;

        if ($overlap < 0 || $overlap >= $chunkSize) {
            throw new InvalidArgumentException('Overlap must be non-negative and less than chunk size.');
        }

        foreach ($documents as $document) {
            if (mb_strlen($document->content) <= $chunkSize) {
                yield $document;

                continue;
            }

            $text = $document->content;
            $length = mb_strlen($text);
            $start = 0;

            while ($start < $length) {
                $end = min($start + $chunkSize, $length);
                $chunkText = mb_substr($text, $start, $end - $start);

                yield new TextDocument(Uuid::v4(), $chunkText, new Metadata([
                    'parent_id' => $document->id,
                    'text' => $chunkText,
                    ...$document->metadata,
                ]));

                $start += ($chunkSize - $overlap);
            }
        }
    }
}
