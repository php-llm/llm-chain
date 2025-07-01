<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
interface ContractInterface
{
    /**
     * @param object|array<string|int, mixed>|string $input
     *
     * @return array<string, mixed>|string
     */
    public function createRequestPayload(Model $model, object|array|string $input): string|array;

    /**
     * @param Tool[] $tools
     *
     * @return array<string, mixed>
     */
    public function createToolOption(array $tools, Model $model): array;
}
