<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAICompatible;

use Aws\Credentials\Credentials;
use PhpLlm\LlmChain\Bridge\AwsBedrock\BedrockRequestSigner;
use PhpLlm\LlmChain\Bridge\AwsBedrock\Language\ModelClient as LanguageModelClient;
use PhpLlm\LlmChain\Bridge\AwsBedrock\Language\ResponseConverter as LanguageResponseConverter;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlatformFactory
{
    public static function create(
        #[\SensitiveParameter]
        array $credentials,
        string $region,
        ?HttpClientInterface $httpClient = null,
    ): Platform {
        $httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        $requesterSigner = new BedrockRequestSigner(
            new Credentials(
                key: $credentials['key'] ?? null,
                secret: $credentials['secret'] ?? null,
                token: $credentials['token'] ?? null,
            ),
            $region
        );

        return new Platform([
            new LanguageModelClient($httpClient, $requesterSigner, $region),
        ], [
            new LanguageResponseConverter(),
        ]);
    }
}
