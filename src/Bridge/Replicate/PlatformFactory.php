<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate;

use PhpLlm\LlmChain\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        return new Platform(
            [new LlamaModelClient(new Client(HttpClient::create(), new Clock(), $apiKey), new LlamaPromptConverter())],
            [new LlamaResponseConverter()],
        );
    }
}
