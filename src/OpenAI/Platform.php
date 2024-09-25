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

namespace PhpLlm\LlmChain\OpenAI;

interface Platform
{
    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function request(string $endpoint, array $body): array;

    /**
     * @param array<array<string, mixed>> $bodies
     *
     * @return \Generator<array<string, mixed>>
     */
    public function multiRequest(string $endpoint, array $bodies): \Generator;
}
