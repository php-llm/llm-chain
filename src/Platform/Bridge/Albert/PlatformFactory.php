<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Platform;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    /**
     * Creates a Platform instance for Albert API.
     */
    public static function create(
        string $apiKey,
        string $albertUrl,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient ??= HttpClient::create();

        return new Platform(
            $httpClient,
            $apiKey,
            rtrim($albertUrl, '/').'/v1/',
        );
    }
}