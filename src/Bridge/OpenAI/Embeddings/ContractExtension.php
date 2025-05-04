<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\Contract\Extension;

final class ContractExtension implements Extension
{
    public function supports(Model $model): bool
    {
        return $model instanceof Embeddings;
    }

    public function registerTypes(): array
    {
        return [
            'string' => 'handleInput',
        ];
    }

    public function handleInput(string $input): array
    {
        return [
            'input' => $input,
        ];
    }
}
