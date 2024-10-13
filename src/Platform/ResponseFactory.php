<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Model\Model;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface ResponseFactory
{
    /**
     * @param array<mixed>|string|object $input
     */
    public function supports(Model $model, array|string|object $input): bool;

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    public function create(Model $model, array|string|object $input, array $options = []): ResponseInterface;
}
