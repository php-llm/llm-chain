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

namespace PhpLlm\LlmChain\Message;

final readonly class SystemMessage implements MessageInterface
{
    public function __construct(public string $content)
    {
    }

    public function getRole(): Role
    {
        return Role::System;
    }

    /**
     * @return array{
     *     role: Role::System,
     *     content: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'role' => Role::System,
            'content' => $this->content,
        ];
    }
}
