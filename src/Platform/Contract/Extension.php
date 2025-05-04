<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract;

use PhpLlm\LlmChain\Model\Model;

interface Extension
{
    /**
     * Defines if the extension supports the given model.
     */
    public function supports(Model $model): bool;

    /**
     * Returns a list of types with their corresponding handler to replace the default.
     *
     * @return array<class-string, string>
     */
    public function registerTypes(): array;
}
