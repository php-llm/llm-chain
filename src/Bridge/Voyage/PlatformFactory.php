<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Voyage;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $handler = new ModelHandler($httpClient, $apiKey);

        return new Platform([$handler], [$handler]);
    }
}
