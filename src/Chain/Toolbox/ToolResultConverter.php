<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

final readonly class ToolResultConverter
{
    /**
     * @param \JsonSerializable|\Stringable|array<int|string, mixed>|float|string|null $result
     */
    public function convert(\JsonSerializable|\Stringable|array|float|string|null $result): ?string
    {
        if (null === $result) {
            return null;
        }

        if ($result instanceof \JsonSerializable || is_array($result)) {
            return json_encode($result, flags: JSON_THROW_ON_ERROR);
        }

        if (is_float($result) || $result instanceof \Stringable) {
            return (string) $result;
        }

        return $result;
    }
}
