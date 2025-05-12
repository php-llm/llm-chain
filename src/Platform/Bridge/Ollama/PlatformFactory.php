<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Ollama;

use PhpLlm\LlmChain\Platform\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    public static function create(
        string $hostUrl = 'http://localhost:11434',
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        $handler = new LlamaModelHandler($httpClient, $hostUrl);

        return new Platform([$handler], [$handler]);
    }
}
