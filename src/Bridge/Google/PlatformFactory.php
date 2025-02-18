<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $responseHandler = new ModelHandler($httpClient, $apiKey);

        return new Platform([$responseHandler], [$responseHandler]);
    }
}
