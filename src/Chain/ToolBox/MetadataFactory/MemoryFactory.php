<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolMetadataException;

final class MemoryFactory extends AbstractFactory
{
    /**
     * @var array<string, AsTool[]>
     */
    private array $tools = [];

    public function addTool(string $className, string $name, string $description, string $method = '__invoke'): self
    {
        $this->tools[$className][] = new AsTool($name, $description, $method);

        return $this;
    }

    /**
     * @param class-string $reference
     */
    public function getMetadata(string $reference): iterable
    {
        if (!isset($this->tools[$reference])) {
            throw ToolMetadataException::invalidReference($reference);
        }

        foreach ($this->tools[$reference] as $tool) {
            yield $this->convertAttribute($reference, $tool);
        }
    }
}
