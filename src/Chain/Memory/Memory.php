<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Memory;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class Memory
{
    public function __construct(public string $content)
    {
    }
}
