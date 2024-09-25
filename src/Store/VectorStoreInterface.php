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
use PhpLlm\LlmChain\Document\Vector;

interface VectorStoreInterface extends StoreInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @return Document[]
     */
    public function query(Vector $vector, array $options = []): array;
}
