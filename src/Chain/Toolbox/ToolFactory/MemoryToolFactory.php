<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\ToolFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolException;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class MemoryToolFactory extends AbstractToolFactory
{
    /**
     * @var array<string, AsTool[]>
     */
    private array $tools = [];

    public function addTool(string|object $class, string $name, string $description, string $method = '__invoke'): self
    {
        $className = \is_object($class) ? $class::class : $class;
        $this->tools[$className][] = new AsTool($name, $description, $method);

        return $this;
    }

    /**
     * @param class-string $reference
     */
    public function getTool(string $reference): iterable
    {
        if (!isset($this->tools[$reference])) {
            throw ToolException::invalidReference($reference);
        }

        foreach ($this->tools[$reference] as $tool) {
            yield $this->convertAttribute($reference, $tool);
        }
    }
}
