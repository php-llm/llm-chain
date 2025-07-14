<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Memory;

use PhpLlm\LlmChain\Chain\Input;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class StaticMemoryProvider implements MemoryProviderInterface
{
    /**
     * @var array<string>
     */
    private array $memory;

    public function __construct(string ...$memory)
    {
        $this->memory = $memory;
    }

    public function loadMemory(Input $input): array
    {
        if (0 === \count($this->memory)) {
            return [];
        }

        $content = '## Static Memory'.\PHP_EOL;

        foreach ($this->memory as $memory) {
            $content .= \PHP_EOL.'- '.$memory;
        }

        return [new Memory($content)];
    }
}
