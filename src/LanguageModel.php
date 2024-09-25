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

use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\Response;

interface LanguageModel
{
    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): Response;

    public function supportsToolCalling(): bool;

    public function supportsStructuredOutput(): bool;
}
