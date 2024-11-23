<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Voyage;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\HttpClient;

final class PlatformFactory
{
    public static function create(string $apiKey): Platform
    {
        $handler = new ModelHandler(HttpClient::create(), $apiKey);

        return new Platform([$handler], [$handler]);
    }
}
