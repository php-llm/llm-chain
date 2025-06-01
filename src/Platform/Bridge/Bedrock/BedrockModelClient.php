<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock;

use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;

/**
 * @author Björn Altmann
 */
interface BedrockModelClient
{
    public function supports(Model $model): bool;

    /**
     * @param array<mixed>|string  $payload
     * @param array<string, mixed> $options
     */
    public function request(Model $model, array|string $payload, array $options = []): LlmResponse;
}
