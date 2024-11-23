<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\EventSourceHttpClient;

final readonly class PlatformFactory
{
    public static function create(#[\SensitiveParameter] string $apiKey): Platform
    {
        $responseHandler = new ModelHandler(new EventSourceHttpClient(), $apiKey);

        return new Platform([$responseHandler], [$responseHandler]);
    }
}
