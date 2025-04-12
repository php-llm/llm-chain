<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Azure\Meta;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlatformFactory
{
    public static function create(
        string $baseUrl,
        #[\SensitiveParameter]
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $modelClient = new LlamaHandler($httpClient ?? HttpClient::create(), $baseUrl, $apiKey);

        return new Platform([$modelClient], [$modelClient]);
    }
}
