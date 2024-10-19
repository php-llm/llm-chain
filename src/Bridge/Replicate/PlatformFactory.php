<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate;

use PhpLlm\LlmChain\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Platform;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpClient\HttpClient;

final class PlatformFactory
{
    public static function create(string $apiKey): Platform
    {
        return new Platform(
            [new LlamaModelClient(new Client(HttpClient::create(), new Clock(), $apiKey), new LlamaPromptConverter())],
            [new LlamaResponseConverter()],
        );
    }
}
