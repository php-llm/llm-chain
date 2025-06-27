<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Document;

/**
 * A Transformer is designed to mutate a stream of TextDocuments with the purpose of preparing them for indexing.
 * It can reduce or expand the number of documents, modify their content or metadata.
 * It should not act blocking, but is expected to iterate over incoming documents and yield prepared ones.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface TransformerInterface
{
    /**
     * @param iterable<TextDocument> $documents
     * @param array<string, mixed>   $options
     *
     * @return iterable<TextDocument>
     */
    public function __invoke(iterable $documents, array $options = []): iterable;
}
