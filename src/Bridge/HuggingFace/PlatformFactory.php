<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\HuggingFace;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        string $provider = Provider::HF_INFERENCE,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform([new ModelClient($httpClient, $provider, $apiKey)], [new ResponseConverter()]);
    }
}
