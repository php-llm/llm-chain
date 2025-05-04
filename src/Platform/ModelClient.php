<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Model\Model;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface ModelClient
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface;
}
