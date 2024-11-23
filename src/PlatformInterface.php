<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;

interface PlatformInterface
{
    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    public function request(Model $model, array|string|object $input, array $options = []): ResponseInterface;
}
