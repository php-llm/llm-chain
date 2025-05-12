<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

interface ResponseConverterInterface
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed> $options
     */
    public function convert(HttpResponse $response, array $options = []): LlmResponse;
}
