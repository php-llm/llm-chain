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

namespace PhpLlm\LlmChain\Exception;

final class MessageBagNotFound extends \DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('MessageBag with id "%s" not found', $id));
    }
}
