<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface as PlatformResponseFactory;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelClient implements PlatformResponseFactory
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::startsWith($apiKey, 'sk-', 'The API key must start with "sk-".');
    }

    public function supports(Model $model): bool
    {
        return $model instanceof GPT;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, $payload),
        ]);
    }
}
