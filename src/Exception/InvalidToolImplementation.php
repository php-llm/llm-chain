<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Exception;

use PhpLlm\LlmChain\ToolBox\AsTool;

final class InvalidToolImplementation extends \InvalidArgumentException
{
    public static function missingAttribute(string $className): self
    {
        return new self(sprintf('The class "%s" is not a tool, please add %s attribute.', $className, AsTool::class));
    }
}
