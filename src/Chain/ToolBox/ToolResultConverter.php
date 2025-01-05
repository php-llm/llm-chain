<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

final readonly class ToolResultConverter
{
    public function convert(mixed $result): string
    {
        if ($result instanceof \JsonSerializable || is_array($result)) {
            return json_encode($result, flags: JSON_THROW_ON_ERROR);
        }

        if (is_integer($result) || is_float($result) || $result instanceof \Stringable) {
            return (string) $result;
        }

        return $result;
    }
}
