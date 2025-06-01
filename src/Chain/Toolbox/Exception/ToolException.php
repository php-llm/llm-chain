<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Exception;

use PhpLlm\LlmChain\Chain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ToolException extends InvalidArgumentException implements ExceptionInterface
{
    public static function invalidReference(mixed $reference): self
    {
        return new self(\sprintf('The reference "%s" is not a valid tool.', $reference));
    }

    public static function missingAttribute(string $className): self
    {
        return new self(\sprintf('The class "%s" is not a tool, please add %s attribute.', $className, AsTool::class));
    }
}
