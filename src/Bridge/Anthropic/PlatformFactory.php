<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;

final readonly class PlatformFactory
{
    public static function create(#[\SensitiveParameter] string $apiKey, string $version = '2023-06-01'): Platform
    {
        $responseHandler = new ModelHandler(new EventSourceHttpClient(), $apiKey, $version);

        return new Platform([$responseHandler], [$responseHandler]);
    }
}
