<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolMetadataException;
use PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory;

final readonly class ChainFactory implements MetadataFactory
{
    /**
     * @var list<MetadataFactory>
     */
    private array $factories;

    /**
     * @param iterable<MetadataFactory> $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories instanceof \Traversable ? iterator_to_array($factories) : $factories;
    }

    public function getMetadata(string $reference): iterable
    {
        $invalid = 0;
        foreach ($this->factories as $factory) {
            try {
                yield from $factory->getMetadata($reference);
            } catch (ToolMetadataException) {
                ++$invalid;
                continue;
            }

            // If the factory does not throw an exception, we don't need to check the others
            return;
        }

        if ($invalid === count($this->factories)) {
            throw ToolMetadataException::invalidReference($reference);
        }
    }
}
