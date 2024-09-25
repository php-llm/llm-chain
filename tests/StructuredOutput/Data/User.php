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

namespace PhpLlm\LlmChain\Tests\StructuredOutput\Data;

final class User
{
    public int $id;
    /**
     * @var string The name of the user in lowercase
     */
    public string $name;
    public \DateTimeInterface $createdAt;
    public bool $isActive;
}
