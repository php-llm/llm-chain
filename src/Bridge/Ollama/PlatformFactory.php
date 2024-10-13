<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Ollama;

use PhpLlm\LlmChain\Platform;
use Symfony\Component\HttpClient\HttpClient;

final class PlatformFactory
{
    public static function create(string $hostUrl = 'http://localhost:11434'): Platform
    {
        $handler = new LlamaModelHandler(HttpClient::create(), $hostUrl);

        return new Platform([$handler], [$handler]);
    }
}
