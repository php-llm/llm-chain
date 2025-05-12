<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface ModelClientInterface
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface;
}
