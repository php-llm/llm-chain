<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock;

use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;

interface BedrockModelClient
{
    /**
     * @param array<mixed>|string|object $input
     */
    public function supports(Model $model, array|string|object $input): bool;

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    public function request(Model $model, array|string|object $input, array $options = []): LlmResponse;
}
