<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Exception;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;

final class ToolMetadataException extends InvalidArgumentException implements ExceptionInterface
{
    public static function invalidReference(mixed $reference): self
    {
        return new self(sprintf('The reference "%s" is not a valid as tool.', $reference));
    }

    public static function missingAttribute(string $className): self
    {
        return new self(sprintf('The class "%s" is not a tool, please add %s attribute.', $className, AsTool::class));
    }
}
