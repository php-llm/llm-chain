<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Store;

use PhpLlm\LlmChain\Document\Document;

interface StoreInterface
{
    public function addDocument(Document $document): void;

    /**
     * @param list<Document> $documents
     */
    public function addDocuments(array $documents): void;
}
