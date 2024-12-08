<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        string $version = '2023-06-01',
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $responseHandler = new ModelHandler($httpClient, $apiKey, $version);

        return new Platform([$responseHandler], [$responseHandler]);
    }
}
