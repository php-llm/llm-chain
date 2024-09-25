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

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Document\Vector;

interface EmbeddingModel
{
    public function create(string $text): Vector;

    /**
     * @param list<string> $texts
     *
     * @return list<Vector>
     */
    public function multiCreate(array $texts): array;
}
