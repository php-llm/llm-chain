<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface ModelClientInterface
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface;
}
