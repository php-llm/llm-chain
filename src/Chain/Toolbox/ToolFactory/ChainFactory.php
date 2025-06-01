<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\ToolFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolException;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactoryInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ChainFactory implements ToolFactoryInterface
{
    /**
     * @var list<ToolFactoryInterface>
     */
    private array $factories;

    /**
     * @param iterable<ToolFactoryInterface> $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories instanceof \Traversable ? iterator_to_array($factories) : $factories;
    }

    public function getTool(string $reference): iterable
    {
        $invalid = 0;
        foreach ($this->factories as $factory) {
            try {
                yield from $factory->getTool($reference);
            } catch (ToolException) {
                ++$invalid;
                continue;
            }

            // If the factory does not throw an exception, we don't need to check the others
            return;
        }

        if ($invalid === \count($this->factories)) {
            throw ToolException::invalidReference($reference);
        }
    }
}
