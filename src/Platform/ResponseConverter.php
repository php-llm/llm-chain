<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

interface ResponseConverter
{
    /**
     * @param array<string, mixed>|string|object $input
     */
    public function supports(Model $model, array|string|object $input): bool;

    /**
     * @param array<string, mixed> $options
     */
    public function convert(HttpResponse $response, array $options = []): LlmResponse;
}
