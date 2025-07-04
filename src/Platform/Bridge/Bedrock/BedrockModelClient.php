<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock;

use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;

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
    public function request(Model $model, array|string $payload, array $options = []): InvokeModelResponse;

    public function convert(InvokeModelResponse $bedrockResponse): ResponseInterface;
}
