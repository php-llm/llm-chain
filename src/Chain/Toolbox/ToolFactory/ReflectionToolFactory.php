<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\ToolFactory;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolException;

/**
 * Metadata factory that uses reflection in combination with `#[AsTool]` attribute to extract metadata from tools.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ReflectionToolFactory extends AbstractToolFactory
{
    /**
     * @param class-string $reference
     */
    public function getTool(string $reference): iterable
    {
        if (!class_exists($reference)) {
            throw ToolException::invalidReference($reference);
        }

        $reflectionClass = new \ReflectionClass($reference);
        $attributes = $reflectionClass->getAttributes(AsTool::class);

        if (0 === \count($attributes)) {
            throw ToolException::missingAttribute($reference);
        }

        foreach ($attributes as $attribute) {
            yield $this->convertAttribute($reference, $attribute->newInstance());
        }
    }
}
