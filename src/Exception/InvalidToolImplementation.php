<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain\Exception;

final class InvalidToolImplementation extends \InvalidArgumentException
{
    public static function missingAttribute(string $className): self
    {
        return new self(sprintf('The class "%s" is not a tool, please add AsTool attribute.', $className));
    }
}
